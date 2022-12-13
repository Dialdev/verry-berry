<?php

namespace Natix\Module\Api\Slim;

/**
 * Отнаследованный роутер, чтобы создать свою версию Route
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Router extends \Slim\Router
{
    /**
     * {@inheritDoc}
     */
    protected function createRoute($methods, $pattern, $callable)
    {
        $route = new Route($methods, $pattern, $callable, $this->routeGroups, $this->routeCounter);
        if (!empty($this->container)) {
            $route->setContainer($this->container);
        }

        return $route;
    }
}
