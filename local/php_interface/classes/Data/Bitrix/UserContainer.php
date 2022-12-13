<?php

namespace Natix\Data\Bitrix;

use Bitrix\Main\UserTable;
use Natix\Data\Bitrix\Exception\UserContainerException;

/**
 * Обёртка для глобального объекта $USER
 */
class UserContainer implements UserContainerInterface
{
    /** @var \CUser */
    private $obUser;

    /**
     * @var array
     */
    private $userData = [];

    public function __construct()
    {
        /** @var \CUser */
        global $USER;

        if (!(is_object($USER) && $USER instanceof \CUser)) {
            $USER = new \CUser();
        }

        $this->obUser = $USER;

        $this->refreshUserData();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->obUser->GetID() ? intval($this->obUser->GetID()) : null;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->obUser->GetFirstName() ? $this->obUser->GetFirstName() : null;
    }

    /**
     * @return string|null
     */
    public function getSecondName()
    {
        return $this->obUser->GetSecondName() ? $this->obUser->GetSecondName() : null;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->obUser->GetLastName() ? $this->obUser->GetLastName() : null;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->obUser->GetEmail() ? $this->obUser->GetEmail() : null;
    }

    /**
     * @return string|null
     */
    public function getPersonalPhone()
    {
        return isset($this->userData['PERSONAL_PHONE']) ? $this->userData['PERSONAL_PHONE'] : null;
    }

    /**
     * @return mixed|null
     */
    public function getPersonalMobile()
    {
        return isset($this->userData['PERSONAL_MOBILE']) ? $this->userData['PERSONAL_MOBILE'] : null;
    }

    /**
     * @return mixed|null
     */
    public function getPersonalBirthday()
    {
        return isset($this->userData['PERSONAL_BIRTHDAY']) ? $this->userData['PERSONAL_BIRTHDAY'] : null;
    }

    /**
     * @return string|null
     */
    public function getFiasCodeCity()
    {
        return isset($this->userData['UF_FIAS_CODE_CITY']) ? $this->userData['UF_FIAS_CODE_CITY'] : null;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getById($userId)
    {
        return $this->obUser->GetById($userId)->Fetch();
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->obUser->IsAuthorized();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->obUser->IsAdmin();
    }

    public function authorize(int $userId, bool $save = false, $update = true)
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('Ид пользователя должно быть больше нуля');
        }

        $this->logout();

        return $this->obUser->Authorize($userId, $save, $update);
    }

    public function login($login, $password, $remember = 'N')
    {
        $result = $this->obUser->Login($login, $password, $remember);

        if ($result === true) {
            $this->refreshUserData();
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function logout()
    {
        if ($this->isAuthorized()) {
            $this->obUser->Logout();
        }

        return true;
    }

    /**
     * Обновляет данные в переменной $this->userData в зависимости от авторизованности пользователя
     */
    private function refreshUserData()
    {
        if (
            $this->isAuthorized()
            && $this->getId() > 0
        ) {
            $userRow = UserTable::getRow([
                'select' => [
                    'PERSONAL_PHONE',
                    'PERSONAL_MOBILE',
                    'PERSONAL_BIRTHDAY',
                    //'UF_NAZ',
                    //'UF_INN',
                    //'UF_KPP',
                    //'UF_KOR',
                    //'UF_BIK',
                    //'UF_NAIM',
                    //'UF_UR_ADDRESS',
                    //'UF_CHECKING_ACCOUNT',
                    //'UF_MNOGORU_CARD',
                    //'UF_BLOCK_MAKE_ORDER',
                    //'UF_FIAS_CODE_CITY'
                ],
                'filter' => [
                    '=ID' => $this->getId(),
                ]
            ]);

            if (is_array($userRow)) {
                $this->userData = $userRow;
            }
        } else {
            $this->userData = [];
        }
    }

    /**
     * Название организации
     *
     * @return string|null
     */
    public function getLegalNameOrganization()
    {
        return isset($this->userData['UF_NAZ']) ? $this->userData['UF_NAZ'] : null;
    }

    /**
     * ИНН
     *
     * @return string|null
     */
    public function getLegalInn()
    {
        return isset($this->userData['UF_INN']) ? $this->userData['UF_INN'] : null;
    }

    /**
     * КПП
     *
     * @return string|null
     */
    public function getLegalKpp()
    {
        return isset($this->userData['UF_KPP']) ? $this->userData['UF_KPP'] : null;
    }

    /**
     * Корреспондентский счёт
     *
     * @return string|null
     */
    public function getLegalCorrespondentAccount()
    {
        return isset($this->userData['UF_KOR']) ? $this->userData['UF_KOR'] : null;
    }

    /**
     * БИК
     *
     * @return string|null
     */
    public function getLegalBik()
    {
        return isset($this->userData['UF_BIK']) ? $this->userData['UF_BIK'] : null;
    }

    /**
     * Наименование банка
     *
     * @return string|null
     */
    public function getLegalNameBank()
    {
        return isset($this->userData['UF_NAIM']) ? $this->userData['UF_NAIM'] : null;
    }

    /**
     * Юридический адрес
     *
     * @return string|null
     */
    public function getLegalAddress()
    {
        return isset($this->userData['UF_UR_ADDRESS']) ? $this->userData['UF_UR_ADDRESS'] : null;
    }

    /**
     * Расчетный счет
     *
     * @return string|null
     */
    public function getLegalCheckingAccount()
    {
        return isset($this->userData['UF_CHECKING_ACCOUNT']) ? $this->userData['UF_CHECKING_ACCOUNT'] : null;
    }

    /**
     * @param array $fields
     *
     * @return bool|int|string
     * @throws UserContainerException
     */
    public function add(array $fields)
    {
        $userId = $this->obUser->Add($fields);

        if (!$userId) {
            throw new UserContainerException(
                sprintf(
                    'Ошибка добавления нового пользователя: %s',
                    $this->obUser->LAST_ERROR
                )
            );
        }

        return $userId;
    }

    /**
     * @param int $id
     * @param array $fields
     * @return bool|int|string
     * @throws UserContainerException
     */
    public function update(int $id, array $fields)
    {
        $userId = $this->obUser->update($id, $fields);

        if (!$userId) {
            throw new UserContainerException(
                sprintf(
                    'Ошибка при обновление данных пользователя с id=%s: %s',
                    $id,
                    $this->obUser->LAST_ERROR
                )
            );
        }

        return $userId;
    }

    /**
     * @return array
     */
    public function getUserGroupArray()
    {
        return $this->obUser->GetUserGroupArray();
    }
}
