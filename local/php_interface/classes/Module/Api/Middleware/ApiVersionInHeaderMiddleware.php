<?php

namespace Natix\Module\Api\Middleware;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ApiVersionInHeaderMiddleware
{
    /**
     * Middleware добавляет в заголовки версию нашего апи
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param callable $next Next middleware
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $response = $response->withHeader('X-Natix-Version-Api', '1.0');

        return $next($request, $response);
    }
}
