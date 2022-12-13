<?php

namespace Natix\Service\Identification\Token;

use Natix\Service\Identification\Token\Entity\Token;
use Natix\Service\Identification\Token\Exception\TokenException;

/**
 * Хранилище на хите для токена, который передан в заголовке X-TOKEN
 * CheckBitrixSessidOrTokenMiddleware проверяет переданный в заголовке токен
 * И если токен проходит все проверки, он устанавливается в хранилище
 * Таким образом $this->token либо null либо валидный токен
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ApiTokenRuntimeStorage
{
    /**
     * @var Token|null
     */
    private $token;

    /**
     * @var string Код токена, переданный в заголовке запроса
     */
    private $tokenRequestString;

    /**
     * @param Token $token
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
    }

    /**
     * Проверят, установлен ли токен в хранилище
     * Возможно авторизация прошла по парамету SESSID, тогда токена не будет
     * @return bool
     */
    public function isTokenSet(): bool
    {
        return $this->token !== null;
    }

    /**
     * @return null|Token
     * @throws TokenException
     */
    public function getToken()
    {
        if ($this->token === null) {
            throw new TokenException('Токен не установлен в хранилище');
        }

        return $this->token;
    }

    /**
     * @param string $tokenRequestString
     */
    public function setTokenRequestString(string $tokenRequestString)
    {
        $this->tokenRequestString = $tokenRequestString;
    }

    /**
     * @return string|null
     */
    public function getTokenRequestString()
    {
        return $this->tokenRequestString;
    }
}
