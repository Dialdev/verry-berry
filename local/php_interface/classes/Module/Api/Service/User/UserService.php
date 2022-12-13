<?php

namespace Natix\Module\Api\Service\User;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\UserTable;
use Bitrix\Sale\PersonType;
use Natix\Data\Bitrix\UserContainer;
use Natix\Data\Bitrix\UserContainerInterface;
use Natix\Module\Api\Exception\User\UserAuthorizationException;
use Natix\Module\Api\Exception\User\UserServiceException;
use Natix\Service\Tools\Data\PhoneNumber\PhoneNumberService;
use Natix\Service\Tools\Data\PhoneNumber\PhoneNumberServiceException;
use Psr\Log\LoggerInterface;

/**
 * Class UserService
 *
 * @package Natix\Module\Api\Service\User
 */
class UserService
{
    /** @var PhoneNumberService */
    private $phoneNumberService;

    /** @var \CUser */
    private $userRepo;

    /** @var \CSaleOrderUserProps */
    private $userProfileRepo;

    /** @var UserContainerInterface */
    private $userContainer;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Обязательные поля метода добавления нового пользователя
     *
     * @var array
     */
    private $addRequiredFields = [
        'EMAIL',
        'NAME',
        'PERSONAL_PHONE',
        'PASSWORD',
        'CONFIRM_PASSWORD',
    ];

    /**
     * Обязательные поля метода обновления пользователя
     *
     * @var array
     */
    private $updateRequiredFields = [
        'EMAIL',
        'NAME',
        'PERSONAL_PHONE',
    ];

    /**
     * Обязательные поля метода авторизации пользователя
     *
     * @var array
     */
    private $loginRequiredFields = [
        'LOGIN',
        'PASSWORD',
    ];

    /**
     * UserService constructor.
     */
    public function __construct()
    {
        $this->injectDependencies();
    }

    /**
     * Подключение зависимостей класса
     */
    private function injectDependencies()
    {
        $this->phoneNumberService = new PhoneNumberService();
        $this->userRepo = new \CUser();
        $this->userProfileRepo = new \CSaleOrderUserProps();
        $this->userContainer = new UserContainer();
        $this->logger = \Natix::$container->get(LoggerInterface::class);
    }

    /**
     * Возвращает id пользователя по e-mail или null, если пользователь не найден
     * @param string $email
     * @return int|null
     */
    public function getUserIdByEmail(string $email): ?int
    {
        if (!empty($email)) {
            $res = UserTable::getRow([
                'filter' => [
                    '=EMAIL' => $email,
                ],
                'select' => ['ID'],
            ]);

            if (isset($res['ID'])) {
                return (int)$res['ID'];
            }
        }

        return null;
    }

    /**
     * Выполняет проверку передачи обязательных полей
     *
     * @param array $fields
     * @param array $listOfRequiredFields
     * @return bool
     * @throws UserServiceException
     */
    private function isRequiredFieldsFilled(array $fields = [], array $listOfRequiredFields = []): bool
    {
        if (empty($listOfRequiredFields)) {
            throw new \InvalidArgumentException('Не передан список обязательных полей');
        }

        foreach ($listOfRequiredFields as $requiredFieldName) {
            if (!isset($fields[$requiredFieldName])) {
                throw new \InvalidArgumentException(sprintf('Не заполнен обязательный параметр %s', $requiredFieldName));
            }
        }

        return true;
    }

    /**
     * @return bool|string
     */
    public function generateNewPassword()
    {
        return substr(uniqid(rand(), true), 0, 6);
    }

    /**
     * Возвращает массив данных для добавления пользователя битрикс
     *
     * @param array $userFields
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    private function generateNewBitrixUserFields(array $userFields): array
    {
        $defGroup = Option::get('main', 'new_user_registration_def_group', '');

        return [
            'LID' => SITE_ID,
            'LOGIN' => $userFields['EMAIL'],
            'EMAIL' => $userFields['EMAIL'],
            'NAME' => $userFields['NAME'],
            'SECOND_NAME' => $userFields['SECOND_NAME'] ?? '',
            'LAST_NAME' => $userFields['LAST_NAME'] ?? '',
            'PERSONAL_PHONE' => $userFields['PERSONAL_PHONE'],
            'ACTIVE' => 'Y',
            'PASSWORD' => $userFields['PASSWORD'],
            'CONFIRM_PASSWORD' => $userFields['CONFIRM_PASSWORD'],
            'UF_PASSWORD' => $userFields['PASSWORD'],
            'GROUP_ID' => explode(',', $defGroup),
        ];
    }

    private function validateInn($inn): bool
    {
        if (!$inn) {
            return false;
        }
        if (!is_numeric($inn)) {
            return false;
        }
        return strlen($inn) === 10 || strlen($inn) === 12;
    }

    private function validateOrganizationName($name): bool
    {
        return !(empty($name));
    }

    private function validateLegalAddress($address): bool
    {
        return !(empty($address));
    }

    private function validateKPP($kpp): bool
    {
        if (empty($kpp)) {
            return false;
        }
        if (strlen($kpp) !== 9) {
            return false;
        }
        return preg_match('[0-9A-Z]', $kpp) !== false;
    }

    private function validateBIK($bik): bool
    {
        if (!is_numeric($bik)) {
            return false;
        }
        return strlen($bik) === 9;
    }

    private function validateCorrespondedAccount($account): bool
    {
        if (!is_numeric($account)) {
            return false;
        }
        return strlen($account) === 20;
    }

    private function validatePaymentAccount($account): bool
    {
        return $this->validateCorrespondedAccount($account);
    }

    private function validateBankName($bankName): bool
    {
        return !(empty($bankName));
    }

    /**
     * @param string $userPhone
     *
     * @return string
     */
    public function formatUserPhone($userPhone): ?string
    {
        try {
            return $this->phoneNumberService->format($userPhone);
        } catch (PhoneNumberServiceException $e) {
            return $userPhone;
        }
    }

    /**
     * Подготовка полей из запроса перед работой с ними
     *
     * @param array $fields
     * @return array
     */
    private function prepareRequestFields(array $fields = []): array
    {
        foreach ($fields as $key => $val) {
            switch ($key) {
                case 'PERSONAL_PHONE':
                    $fields[$key] = $this->formatUserPhone($val);
                    break;
            }
        }

        return $fields;
    }

    /**
     * Возвращает id типа плательщика,
     * в случае если он не указан явно - возвращает id плательщика "Физическое лицо"
     *
     * @param int|null $personTypeId
     *
     * @return null
     * @throws UserServiceException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getPersonTypeId($personTypeId = null): ?int
    {
        if ($personTypeId <= 0) {
            $personTypes = PersonType::load(SITE_ID);
            foreach ($personTypes as $type) {
                $personTypeId = $type['ID'];
                break;
            }

            unset($personTypes, $type);
        }

        if ($personTypeId <= 0) {
            throw new UserServiceException(
                sprintf('Не удалось определить тип плательщика')
            );
        }

        return $personTypeId;
    }

    /**
     * Добавляет пользователя в БД битрикс и профиль пользователя в интернет-магазине
     *
     * @param array $userRequestFields
     * EMAIL - строка - электронная почта пользователя
     * NAME - строка - Имя пользователя
     * LAST_NAME - строка - Фамилия
     * PERSONAL_PHONE - строка - Телефон
     * PERSONAL_MOBILE - строка - Дополнительный номер телефона
     * PERSON_TYPE_ID - число (необязательный параметр) - ID плательщика. Если не передан - апи должно считать что
     * регается физ лицо
     * AUTHORIZE - строка Y|N. Если значение пустое, по умолчанию ставим Y.
     *
     * @return array
     * @throws UserAuthorizationException
     * @throws UserServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function addUser(array $userRequestFields = []): array
    {
        $this->isRequiredFieldsFilled($userRequestFields, $this->addRequiredFields);
        $userRequestFields = $this->prepareRequestFields($userRequestFields);

        $connection = Application::getConnection();
        $connection->startTransaction();

        try {
            $email = $userRequestFields['EMAIL'];

            $userIdByEmail = $this->getUserIdByEmail($email);

            if ($userIdByEmail > 0) {
                throw new UserServiceException('Пользователь уже зарегистрирован');
            }

            $this->checkUserByEmail($email);

            $newUserFields = $this->generateNewBitrixUserFields($userRequestFields);

            /**
             * Указываем, что регистрация пользователя происходит из корзины.
             * Нужно для скрипта, который находится в init.php и отправляет данные
             * для входа на e-mail зарегистрированного пользователя.
             * Сделал через опцию, т.к. копаться в этом коде это жопа полная.
             * Чел который это делал конечно красава, vue.js подключил, но кабзда как все накручено.
             */
            \Bitrix\Main\Config\Option::set("main","userRegistrationFromBasket","Y");

            $newUserId = $this->addBitrixUser($newUserFields);
            $newUserProfileId = $this->addUserSaleProfile($newUserId, $userRequestFields);

            if ($newUserId <= 0 || $newUserProfileId <= 0) {
                throw new UserServiceException('Произошла ошибка при регистрации пользователя');
            }

            $connection->commitTransaction();

            if (array_key_exists('AUTHORIZE', $userRequestFields)) {
                $doAuthorize = $userRequestFields['AUTHORIZE'] !== 'N';
            } else {
                $doAuthorize = true;
            }

            if ($doAuthorize && !$this->userContainer->authorize($newUserId)) {
                //При ошибке авторизации, метод апи по регистрации пользователя вернёт ошибку
                //Но при этом пользователь в БД будет создан и при повторной попытке регистрации метод вернёт ошибку
                //Возможно стоит как-то по-другому обрабатывать это, но ошибка с авторизацией предположительно будет крайне редкой
                throw new UserAuthorizationException(
                    sprintf('Не удалось авторизовать пользователя %d', $newUserId)
                );
            }

            return [
                'USER_ID' => $newUserId ?: null,
                'USER_PROFILE_ID' => $newUserProfileId ?: null,
            ];
        } catch (UserServiceException $e) {
            \Bitrix\Main\Config\Option::set("main","userRegistrationFromBasket","Y");
            $connection->rollbackTransaction();

            $this->logger->debug(
                json_encode($userRequestFields)
            );

            throw new UserServiceException($e->getMessage());
        }
    }

    /**
     * Создаёт профиль покупателя
     *
     * @param int $userId
     * @param array $userProfileFields
     * @return bool|int
     * @throws UserServiceException
     * @throws \Bitrix\Main\ArgumentException
     */
    public function addUserSaleProfile($userId, array $userProfileFields)
    {
        $newUserProfileId = $this->userProfileRepo->Add([
            'NAME' => $userProfileFields['EMAIL'],
            'USER_ID' => $userId,
            'PERSON_TYPE_ID' => $this->getPersonTypeId($userProfileFields['PERSON_TYPE_ID'] ?: 0),
        ]);

        if (!$newUserProfileId) {
            throw new UserServiceException('Не удалось создать профиль покупателя');
        }

        return $newUserProfileId;
    }

    /**
     * Создаёт пользователя в БД битрикса
     *
     * @param array $userBitrixFields
     *
     * @return int|false
     * @throws UserServiceException
     */
    private function addBitrixUser(array $userBitrixFields = [])
    {
        try {
            $newUserId = $this->userContainer->add($userBitrixFields);
        } catch (\Exception $e) {
            throw new UserServiceException(
                sprintf('Ошибка регистрации нового пользователя: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        \CUser::SendUserInfo($newUserId, SITE_ID, GetMessage('INFO_REQ'), false);

        return $newUserId;
    }

    /**
     * Валидация email адреса
     *
     * @param string $email
     *
     * @return bool
     */
    private function isValidEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Проверяет зарегистрирована ли учётка с указанным email на сайте
     *
     * @param string $email
     * @return array
     * @throws UserServiceException
     */
    public function checkUserByEmail($email): array
    {
        if (!$this->isValidEmail($email)) {
            throw new UserServiceException('Передан некорректный email');
        }

        return [
            'EMAIL' => $email,
            'IS_AVAILABLE' => !$this->getUserIdByEmail($email),
        ];
    }

    /**
     * Возвращает профиль покупателя по id пользователя
     *
     * @param int $userId
     *
     * @return null|array
     * @throws UserServiceException
     */
    private function getUserSaleProfileByUserId($userId): ?array
    {
        if ($userId <= 0) {
            throw new UserServiceException('Не передан id пользователя');
        }

        $userProfile = $this->userProfileRepo->GetList(['ID' => 'DESC'], [
            'USER_ID' => $userId,
        ])->Fetch();

        return $userProfile ?: null;
    }

    /**
     * Возвращает профиль покупателя по его id
     *
     * @param int $userProfileId
     * @param int $userId
     *
     * @return null|array
     * @throws UserServiceException
     */
    private function getUserSaleProfileById($userProfileId, $userId): ?array
    {
        if ($userProfileId <= 0) {
            throw new UserServiceException('Не передан id профиля покупателя');
        }

        $userProfile = $this->userProfileRepo->GetList([], [
            'ID' => $userProfileId,
            'USER_ID' => $userId,
        ])->Fetch();

        return $userProfile ?: null;
    }

    /**
     * Имеет ли пользователь права на обновление данных
     *
     * @param int $userId
     *
     * @return bool
     */
    private function canUserDoUpdate($userId): bool
    {
        global $USER;
        $USER = !is_object($USER) ? new \CUser() : $USER;

        return (int)$USER->GetID() === (int)$userId;
    }

    /**
     * Возвращает массив данных для обновления пользователя битрикс
     *
     * @param array $userFields
     *
     * @return array
     */
    private function generateUpdateBitrixUserFields(array $userFields): array
    {
        return [
            'LOGIN' => $userFields['EMAIL'],
            'EMAIL' => $userFields['EMAIL'],
            'NAME' => $userFields['NAME'],
            'LAST_NAME' => $userFields['LAST_NAME'],
            'SECOND_NAME' => $userFields['SECOND_NAME'] ?? '',
            'PERSONAL_PHONE' => $userFields['PERSONAL_PHONE'],
        ];
    }

    /**
     * Обновляет пользователя в таблице битрикса
     *
     * @param int $userId
     * @param array $updateFields
     * @return bool
     * @throws UserServiceException
     */
    private function updateBitrixUser($userId, array $updateFields): bool
    {
        if (!$this->userRepo->Update($userId, $updateFields)) {
            throw new UserServiceException(
                sprintf(
                    'Произошла ошибка при обновлении пользователя: %s',
                    $this->userRepo->LAST_ERROR
                )
            );
        }

        return true;
    }

    /**
     * Обновляет профиль покупателя
     *
     * @param int $userProfileId
     * @param array $userProfileFields
     *
     * @return bool
     * @throws UserServiceException
     * @throws \Bitrix\Main\ArgumentException
     */
    private function updateUserSaleProfile($userProfileId, array $userProfileFields): bool
    {
        $isUpdated = $this->userProfileRepo->Update($userProfileId, [
            'NAME' => $userProfileFields['EMAIL'],
            'PERSON_TYPE_ID' => $this->getPersonTypeId($userProfileFields['PERSON_TYPE_ID'] ?: 0),
        ]);

        if (!$isUpdated) {
            throw new UserServiceException('Произошла ошибка при обновлении профиля покупателя');
        }

        return true;
    }

    /**
     * Обновляет пользователя в БД битрикс и профиль пользователя в интернет-магазине
     *
     * @param array $requestParams
     * EMAIL - строка - электронная почта пользователя
     * NAME - строка - Имя пользователя
     * LAST_NAME - строка - Фамилия
     * PERSONAL_PHONE - строка - Телефон
     * PERSON_TYPE_ID - число (необязательный параметр) - ID плательщика
     * USER_PROFILE_ID - число (необязательный параметр) - ID профиля пользователя
     *
     * @return array
     * @throws UserServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function updateUser(array $requestParams): array
    {
        $userRequestFields = $requestParams;

        $this->isRequiredFieldsFilled($userRequestFields, $this->updateRequiredFields);
        $userRequestFields = $this->prepareRequestFields($userRequestFields);

        $connection = Application::getConnection();
        $connection->startTransaction();

        try {
            $userId = $this->getUserIdByEmail($userRequestFields['EMAIL']);

            if ($this->canUserDoUpdate($userId)) {
                $userSaleProfile = $userRequestFields['USER_PROFILE_ID'] > 0
                    ? $this->getUserSaleProfileById($userRequestFields['USER_PROFILE_ID'], $userId)
                    : $this->getUserSaleProfileByUserId($userId);

                if ($userSaleProfile === null) {
                    $userSaleProfile = [
                        'ID' => $this->addUserSaleProfile($userId, $userRequestFields),
                    ];
                }

                $updateUserFields = $this->generateUpdateBitrixUserFields($userRequestFields);

                $this->updateBitrixUser($userId, $updateUserFields);
                $this->updateUserSaleProfile($userSaleProfile['ID'], $updateUserFields);
            } else {
                throw new UserServiceException('Ошибка доступа');
            }

            $connection->commitTransaction();

            return [
                'USER_ID' => $userId ?? null,
                'USER_PROFILE_ID' => $userSaleProfile['ID'] ?? null,
            ];
        } catch (UserServiceException $e) {
            $connection->rollbackTransaction();

            throw new UserServiceException($e->getMessage());
        }
    }

    /**
     * @param [] $userRequestFields
     * @return array
     * @throws UserServiceException
     * @throws \Natix\Service\Tools\Data\PhoneNumber\PhoneNumberServiceException
     */
    public function login($userRequestFields): array
    {
        $this->isRequiredFieldsFilled($userRequestFields, $this->loginRequiredFields);

        $userRequestFields = $this->prepareRequestFields($userRequestFields);

        $loginRemember = isset($userRequestFields['REMEMBER']) && in_array($userRequestFields['REMEMBER'], ['N', 'Y'])
            ? $userRequestFields['REMEMBER']
            : null;

        if (is_scalar($loginRemember)) {
            if (!in_array($loginRemember, ['N', 'Y'])) {
                throw new UserServiceException('Параметр REMEMBER передан некорректно. Он может принимать значения: N, Y');
            }
        } else {
            $loginRemember = 'Y';
        }

        if ($this->userContainer->isAuthorized()) {
            throw new UserServiceException(
                sprintf('Пользователь уже авторизован')
            );
        }

        $userIdByEmail = $this->getUserIdByEmail($userRequestFields['LOGIN']);

        if ($userIdByEmail === null || $userIdByEmail === 0) {
            throw new UserServiceException(
                sprintf('Пользователь с логином "%s" не найден', $userRequestFields['LOGIN'])
            );
        }

        $result = $this->userContainer->login(
            $userRequestFields['LOGIN'],
            $userRequestFields['PASSWORD'],
            $loginRemember
        );

        if (
            $result === true
            && !empty($this->userContainer->getId())
        ) {
            return [
                'USER' => [
                    'ID' => $this->userContainer->getId(),
                    'EMAIL' => $this->userContainer->getEmail(),
                    'NAME' => $this->userContainer->getFirstName(),
                    'LAST_NAME' => $this->userContainer->getLastName(),
                    'PERSONAL_PHONE' => $this->userContainer->getPersonalPhone(),
                ],
            ];
        }

        /** @global \CMain $APPLICATION */
        global $APPLICATION;

        if (($ex = $APPLICATION->GetException())) {
            throw new UserServiceException(
                sprintf(
                    'Ошибка авторизации: %s',
                    // Битрикс возвращает ошибки с тегом <br>
                    preg_replace('~<br\w*?\/?>$~', '', $ex->GetString())
                )
            );
        }

        if (
            !is_array($result)
            || !isset($result['TYPE'])
            || $result['TYPE'] !== 'OK'
        ) {
            throw new UserServiceException(
                sprintf(
                    'Ошибка авторизации: %s',
                    // Битрикс возвращает ошибки с тегом <br>
                    preg_replace('~<br\w*?\/?>$~', '', isset($result['MESSAGE']) ? $result['MESSAGE'] : 'unknown')
                )
            );
        }

        return [];
    }

    /**
     * @throws UserServiceException
     */
    public function logout(): void
    {
        $this->userContainer->logout();

        /** @global \CMain $APPLICATION */
        global $APPLICATION;

        if (($ex = $APPLICATION->GetException())) {
            throw new UserServiceException(
                sprintf(
                    'Ошибка разлогинивания: %s',
                    // Битрикс возвращает ошибки с тегом <br>
                    preg_replace('~<br\w*?\/?>$~', '', $ex->GetString())
                )
            );
        }
    }
}
