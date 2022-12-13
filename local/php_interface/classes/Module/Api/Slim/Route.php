<?php

namespace Natix\Module\Api\Slim;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Отнаследованный класс маршрута, чтобы обрабатывать свой вариант респонза, полученного из экшена,
 * для более лаконичных экшенов
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Route extends \Slim\Route
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->callable = $this->resolveCallable($this->callable);

        /** @var InvocationStrategyInterface $handler */
        $handler = isset($this->container) ? $this->container->get('foundHandler') : new RequestResponse();

        $newResponse = $handler($this->callable, $request, $response, $this->arguments);

        if ($newResponse instanceof \Natix\Module\Api\Http\Response\ResponseInterface) {
            $response = $response->withJson($newResponse->toArray(), $newResponse->getHttpStatus());
        } elseif ($newResponse instanceof ResponseInterface) {
            // if route callback returns a ResponseInterface, then use it
            $response = $newResponse;
        } elseif (is_string($newResponse)) {
            // if route callback returns a string, then append it to the response
            if ($response->getBody()->isWritable()) {
                $response->getBody()->write($newResponse);
            }
        }

        return $response;
    }
}
