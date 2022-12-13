<?php

namespace Natix\Service\Identification\Token;

use Psr\Container\ContainerInterface;
use Natix\Service\Identification\Token\Entity\Token;
use Natix\Service\Identification\Token\Exception\TokenException;
use Natix\Service\Identification\Token\Restriction\TokenRestriction;
use Natix\Service\Identification\Token\Storage\TokenStorage;
use Slim\Http\Request;

/**
 * Проверяет переданный токен для доступа к апи и возвращает валидированный токен, либо выбрасывает исключение
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ApiTokenResolver
{
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var array */
    private $defaultRestrictions;

    /** @var ContainerInterface */
    private $container;

    /**
     * ApiTokenResolver constructor.
     * @param ContainerInterface $container
     * @param TokenStorage $tokenStorage
     * @param array $defaultRestrictions Ограничения по-умолчанию для токена (Например проверка на тестовый токен)
     */
    public function __construct(ContainerInterface $container, TokenStorage $tokenStorage, array $defaultRestrictions = [])
    {
        $this->tokenStorage = $tokenStorage;
        $this->defaultRestrictions = $defaultRestrictions;
        $this->container = $container;
    }

    /**
     * Возвращает валидный токен или выбрасывает исключение
     * @param string $tokenCode Код токена
     * @param Request $request Объект запроса
     * @throws TokenException
     * @return Token
     */
    public function resolve(string $tokenCode, Request $request): Token
    {
        $token = $this->tokenStorage->getToken($tokenCode);

        $restrictions = $this->defaultRestrictions + $token->getRestrictions();

        /** @var TokenRestriction $restriction */
        foreach ($restrictions as $restrictionClass => $restrictionParams) {
            /** @var TokenRestriction $restrictionObject */
            $restrictionObject = $this->container->get($restrictionClass);
            $restrictionObject->check($request, $token, $restrictionParams);
        }

        return $token;
    }
}
