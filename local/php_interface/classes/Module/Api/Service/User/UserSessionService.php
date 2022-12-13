<?php

namespace Natix\Module\Api\Service\User;

use Natix\Data\Bitrix\UserContainerInterface;

/**
 * Класс, занимается формированием ключей ответа, в методе [GET] /user-session/
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class UserSessionService
{
    /**
     * @var UserContainerInterface
     */
    private $userContainer;

    public function __construct(UserContainerInterface $userContainer)
    {
        $this->userContainer = $userContainer;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getUserSessionData()
    {
        $userFields = [
            'ID' => $this->userContainer->getId(),
            'EMAIL' => $this->userContainer->getEmail(),
            'NAME' => $this->userContainer->getFirstName(),
            'LAST_NAME' => $this->userContainer->getLastName(),
            'SECOND_NAME' => $this->userContainer->getSecondName(),
            'PERSONAL_PHONE' => $this->userContainer->getPersonalPhone(),
        ];

        return [
            'USER' => $userFields,
        ];
    }
}
