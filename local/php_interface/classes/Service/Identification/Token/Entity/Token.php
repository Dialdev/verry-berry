<?php

namespace Natix\Service\Identification\Token\Entity;

use Natix\Service\Identification\Token\Exception\TokenPermissionNotFoundException;
use Natix\Service\Identification\Token\Permission\TokePermission;
use Natix\Service\Identification\Token\Restriction\TokenRestriction;

/**
 * Сущность токена
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Token
{
    /** @var  string */
    private $tokenCode;

    /** @var  int */
    private $partnerId;

    /** @var  bool */
    private $enabled;

    /** @var  bool */
    private $testToken;

    /**
     * Ограничения токена
     * Класс_ограничения => Параметры
     * @var array
     */
    private $restrictions;

    /**
     * Разрешения токена
     * @var TokePermission[]
     */
    private $permissions;

    public function __construct(
        string $tokenCode,
        array $tokenParams = [],
        array $restrictions = [],
        array $permissions = []
    ) {
        $this->tokenCode = $tokenCode;
        $this->restrictions = $restrictions;
        $this->permissions = [];

        foreach ($permissions as $permission) {
            $this->addPermission($permission);
        }

        if (isset($tokenParams['user_id'])) {
            $this->setPartnerId($tokenParams['user_id']);
        }

        if (isset($tokenParams['enabled'])) {
            $this->setEnabled($tokenParams['enabled']);
        }

        if (isset($tokenParams['test_token'])) {
            $this->setTestToken($tokenParams['test_token']);
        }
    }

    /**
     * @return TokenRestriction[]
     */
    public function getRestrictions(): array
    {
        return $this->restrictions;
    }

    /**
     * @return string
     */
    public function getTokenCode(): string
    {
        return $this->tokenCode;
    }

    /**
     * @return int
     */
    public function getPartnerId(): int
    {
        return $this->partnerId;
    }

    /**
     * @param int $partnerId
     */
    private function setPartnerId(int $partnerId)
    {
        $this->partnerId = $partnerId;
    }

    /**
     * @return bool
     */
    public function isTestToken(): bool
    {
        return $this->testToken;
    }

    /**
     * @param bool $testToken
     */
    private function setTestToken(bool $testToken)
    {
        $this->testToken = $testToken;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    private function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @param string $permissionClassName
     * @return bool
     */
    public function hasPermission(string $permissionClassName): bool
    {
        return array_key_exists($permissionClassName, $this->permissions);
    }

    /**
     * @param string $permissionClassName
     * @return TokePermission
     * @throws TokenPermissionNotFoundException
     */
    public function getPermission(string $permissionClassName): TokePermission
    {
        if (array_key_exists($permissionClassName, $this->permissions)) {
            return $this->permissions[$permissionClassName];
        }

        throw new TokenPermissionNotFoundException();
    }

    /**
     * @param TokePermission $permission
     */
    public function addPermission(TokePermission $permission)
    {
        $this->permissions[get_class($permission)] = $permission;
    }
}
