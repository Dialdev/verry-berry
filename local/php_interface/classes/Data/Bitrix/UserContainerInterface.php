<?php

namespace Natix\Data\Bitrix;

use Natix\Data\Bitrix\Exception\UserContainerException;

/**
 * Интерфейс с оберткой для глобального объекта $USER
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
interface UserContainerInterface
{
    public function getId();

    public function getFirstName();

    public function getSecondName();

    public function getLastName();

    public function getEmail();

    public function getPersonalPhone();

    public function getPersonalMobile();

    public function getLegalNameOrganization();

    public function getLegalInn();

    public function getLegalKpp();

    public function getLegalCorrespondentAccount();

    public function getLegalBik();

    public function getLegalNameBank();

    public function getLegalAddress();

    public function getLegalCheckingAccount();

    public function getById($userId);

    public function isAuthorized();

    public function isAdmin();

    public function authorize(int $userId, bool $save = false, $update = true);

    public function login($login, $password, $remember);

    public function logout();

    public function getUserGroupArray();

    public function getPersonalBirthday();

    /**
     * @param array $fields
     * @throws UserContainerException
     */
    public function add(array $fields);

    /**
     * @param int $id
     * @param array $fields
     * @throws UserContainerException
     */
    public function update(int $id, array $fields);
}
