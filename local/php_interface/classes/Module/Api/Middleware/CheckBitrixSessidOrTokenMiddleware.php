<?php

namespace Natix\Module\Api\Middleware;

use Natix\Module\Api\Exception\Middleware\CheckBitrixSessidMiddlewareException;
use Natix\Service\Identification\Token\ApiTokenResolver;
use Natix\Service\Identification\Token\ApiTokenRuntimeStorage;
use Slim\Http\Request;

class CheckBitrixSessidOrTokenMiddleware
{
    /**
     * @var ApiTokenResolver
     */
    private $tokenResolver;

    /**
     * @var ApiTokenRuntimeStorage
     */
    private $apiTokenRuntimeStorage;

    public function __construct(ApiTokenResolver $tokenResolver, ApiTokenRuntimeStorage $apiTokenRuntimeStorage)
    {
        $this->tokenResolver = $tokenResolver;
        $this->apiTokenRuntimeStorage = $apiTokenRuntimeStorage;
    }

    /**
     * Middleware запускает проверку битриксовой функцией check_bitrix_sessid()
     * В реквесте ищет SESSID и сравнивает его с битриксовым $_SESSION["fixed_session_id"]
     *
     * @param Request $request PSR7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param callable $next Next middleware
     * @return \Psr\Http\Message\ResponseInterface
     * @throws CheckBitrixSessidMiddlewareException
     * @throws \Natix\Service\Identification\Token\Exception\TokenException
     */
    public function __invoke($request, $response, $next)
    {
        /**
         * Проверяет, если токен передан, то проверка будет по нему.
         * Если не передан, то проверка будет как раньше - по параметру SESSID
         */

        $tokenPassed = $this->apiTokenRuntimeStorage->isTokenSet();
        $tokenCode = $this->apiTokenRuntimeStorage->getTokenRequestString();

        if (!$tokenPassed && !empty($tokenCode)) {
            $token = $this->tokenResolver->resolve($tokenCode, $request);
            $this->apiTokenRuntimeStorage->setToken($token);
        }

        return $next($request, $response);
    }
}
