<?php

namespace Natix\Service\Identification\Token\Restriction;

use Natix\Service\Identification\Token\Entity\Token;
use Slim\Http\Request;

interface TokenRestriction
{
    /**
     * Выбрасывает исключение с описанием того, что запрещено токену, если не прошел проверку
     *
     * @param Request $request
     * @param Token $token
     * @param array $restrictionParams - произвольные параметры, например список доступных УРЛ
     */
    public function check(Request $request, Token $token, array $restrictionParams = []);
}
