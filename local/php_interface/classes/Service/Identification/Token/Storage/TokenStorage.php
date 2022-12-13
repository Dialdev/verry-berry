<?php

namespace Natix\Service\Identification\Token\Storage;

use Natix\Service\Identification\Token\Entity\Token;
use Natix\Service\Identification\Token\Exception\TokenNotFoundException;

/**
 * Интерфейс хранилища токенов - некого объекта,
 * который умеет работать с хранилицем токенов, например получить список токенов
 * @package Natix\Service\Identification\Token\Storage
 */
interface TokenStorage
{
    /**
     * Получает объект токена по его коду
     *
     * @param string $tokenCode
     * @throws TokenNotFoundException
     * @return Token
     */
    public function getToken(string $tokenCode): Token;

    /**
     * Получает объект токена по ид партнера
     *
     * @param int $partnerId
     * @return Token
     */
    public function getTokenByPartnerId(int $partnerId): Token;
}
