<?php

namespace Natix\Service\Identification\Token\Restriction;

use Natix\Service\Identification\Token\Entity\Token;
use Natix\Service\Identification\Token\Exception\TokenRestrictionFailedException;
use Slim\Http\Request;

/**
 * Проверяет, разрешен ли токену доступ к определенному УРЛУ
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ByAllowedRoutesTokenRestriction implements TokenRestriction
{
    /**
     * @param Request $request
     * @param Token $token
     * @param array $restrictionParams Массив вида:
     * 'routes' => [
     *  [
     *      'url_mask' => '^/api/v1/sale/order/$', // Маска урл, будет проверяться через preg_match('!{$url_mask}!');
     *      'methods' => ['get', 'post']
     *  ]
     * ]
     *
     * @return bool
     * @throws TokenRestrictionFailedException
     */
    public function check(Request $request, Token $token, array $restrictionParams = [])
    {
        $this->validateParams($restrictionParams);

        $requestedUrl = $request->getUri()->getPath();
        $requestMethod = strtolower($request->getMethod());

        foreach ($restrictionParams['routes'] as $route) {
            if (preg_match(sprintf('!%s!', $route['url_mask']), $requestedUrl)) {
                if (in_array($requestMethod, $route['methods'])) {
                    return true;
                }
            }
        }

        throw new TokenRestrictionFailedException(sprintf('Доступ к url [%s] %s запрещен', $requestMethod, $requestedUrl));
    }

    /**
     * Проверяет формат параметров, выбрасывает исключение в случае ошибки
     * @param array $restrictionParams
     */
    private function validateParams(array $restrictionParams)
    {
        if (!is_array($restrictionParams['routes']) || count($restrictionParams['routes']) == 0) {
            throw new \InvalidArgumentException('Параметр routes должен быть непустым массивом');
        }

        foreach ($restrictionParams['routes'] as $route) {
            if (empty($route['url_mask'])) {
                throw new \InvalidArgumentException('Параметр url_mask не может быть пустым');
            }
            if (!is_array($route['methods']) || count($route['methods']) == 0) {
                throw new \InvalidArgumentException('Параметр methods должен быть непустым массивом');
            }
        }
    }
}
