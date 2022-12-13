<?php

namespace Natix\Module\Api\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ProfilerMiddleware
{
    /**
     * @param  ServerRequestInterface $request PSR7 request
     * @param  ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $startTime = microtime(true);

        $memoryStart = memory_get_usage(true);

        /** @var ResponseInterface $response */
        $response = $next($request, $response);

        $stopTime = microtime(true);

        $memoryStop = memory_get_usage(true);

        return $response
            ->withHeader('X-Memory-Used', sprintf('%.03f MB', (($memoryStop - $memoryStart) / (1024 * 1024))))
            ->withHeader('X-Profiler-Time', $stopTime - $startTime);
    }
}
