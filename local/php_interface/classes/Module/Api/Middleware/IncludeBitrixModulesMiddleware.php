<?php

namespace Natix\Module\Api\Middleware;

use Bitrix\Main\Loader;
use Natix\Module\Api\Exception\Middleware\IncludeBitrixModulesMiddlewareException;

class IncludeBitrixModulesMiddleware
{
    private $modules = [
        'sale',
        'iblock',
        'catalog',
    ];

    /**
     * Загрузка нужных модулей битрикса для API
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Natix\Module\Api\Exception\Middleware\IncludeBitrixModulesMiddlewareException
     * @throws \Bitrix\Main\LoaderException
     */
    public function __invoke($request, $response, $next)
    {
        foreach ($this->modules as $module) {
            if (!Loader::includeModule($module)) {
                throw new IncludeBitrixModulesMiddlewareException();
            }
        }

        return $next($request, $response);
    }
}
