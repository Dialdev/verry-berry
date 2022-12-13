<?php

namespace Natix\Service\Identification\Token\Storage;

use Natix\Service\Identification\Token\Entity\Token;
use Natix\Service\Identification\Token\Exception\TokenException;
use Natix\Service\Identification\Token\Exception\TokenNotFoundException;

/**
 * Статическое хранилище токенов, токены передаются параметром конструктора
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class StaticTokenStorage implements TokenStorage
{
    /** @var array */
    private $availableTokens;

    /**
     * StaticTokenStorage constructor.
     * @param array $availableTokens
     */
    public function __construct(array $availableTokens)
    {
        $this->availableTokens = $availableTokens;
    }

    /**
     * @inheritdoc
     */
    public function getToken(string $tokenCode): Token
    {
        $tokenCode = trim($tokenCode);
        if (empty($tokenCode)) {
            throw new TokenException('Передано пустое значение токена');
        }

        $tokens = $this->getAvailableTokens();

        if (!array_key_exists($tokenCode, $tokens)) {
            throw new TokenNotFoundException(sprintf('Токен %s не найден среди доступных', $tokenCode));
        }

        $tokenData = $tokens[$tokenCode];

        $token = new Token(
            $tokenCode,
            $tokenData['params'] ?? [],
            $tokenData['restrictions'] ?? [],
            $tokenData['permissions'] ?? []
        );

        return $token;
    }

    /**
     * @return array
     */
    private function getAvailableTokens(): array
    {
        return $this->availableTokens;
    }

    /**
     * Получает объект токена по ид партнера
     * Если токенов несколько - получает первый попавшийся
     *
     * @param int $partnerId
     * @return Token
     * @throws TokenNotFoundException
     */
    public function getTokenByPartnerId(int $partnerId): Token
    {
        $partnerTokenData = [];
        
        foreach ($this->getAvailableTokens() as $code => $tokenData) {
            if ($tokenData['params']['user_id'] === $partnerId) {
                $partnerTokenData = $tokenData;
                $partnerTokenData['+code'] = $code;
                break;
            }
        }
        
        if (empty($partnerTokenData)) {
            throw new TokenNotFoundException(sprintf('Не найден токен для партнера %d', $partnerId));
        }

        $token = new Token(
            $partnerTokenData['+code'],
            $partnerTokenData['params'] ?? [],
            $partnerTokenData['restrictions'] ?? [],
            $partnerTokenData['permissions'] ?? []
        );

        return $token;
    }
}
