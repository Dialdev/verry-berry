<?php

namespace Natix\Module\Api\Slim;

use Slim\Http\Environment;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Request extends \Slim\Http\Request
{
    /**
     * Перегрузим слимовский метод createFromEnvironment, чтобы фреймворк всегда думал, чтомы хотим json в ответ
     * @param Environment $environment
     * @return \Slim\Http\Request
     */
    public static function createFromEnvironment(Environment $environment)
    {
        $request = parent::createFromEnvironment($environment);

        $request = $request
            ->withHeader('Accept', 'application/json');

        return $request;
    }
}
