<?php

namespace Natix\Service\Identification\Token\Restriction;

use Natix\Service\Identification\Token\Entity\Token;
use Natix\Service\Identification\Token\Exception\TokenRestrictionFailedException;
use Slim\Http\Request;

class AllowedTokenRestriction implements TokenRestriction
{
    public function check(Request $request, Token $token, array $restrictionParams = [])
    {
        if (!$token->isEnabled()) {
            throw new TokenRestrictionFailedException('Токен запрещен');
        }
    }
}
