<?php

namespace Natix\Service\Identification\Token\Restriction;

use Natix\Service\Identification\Token\Entity\Token;
use Natix\Service\Identification\Token\Exception\TokenRestrictionFailedException;
use Slim\Http\Request;

/**
 * Проверяет активность токена
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class TestTokenRestriction implements TokenRestriction
{
    /** @var bool */
    private $isProductionEnv;

    /**
     * TestTokenRestriction constructor.
     * @param bool $isProductionEnv
     */
    public function __construct(bool $isProductionEnv)
    {
        $this->isProductionEnv = $isProductionEnv;
    }

    public function check(Request $request, Token $token, array $restrictionParams = [])
    {
        if ($token->isTestToken() && $this->isProductionEnv === true) {
            throw new TokenRestrictionFailedException('Токен деактивирован');
        }
    }
}
