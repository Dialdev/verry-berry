<?php

namespace Natix\Module\Api\Middleware;

use Bitrix\Main\HttpResponse;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware портирует битриксовые куки в слимовские и отдаёт их в ответе
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BitrixCookieAdapterMiddleware
{
    /**
     * @var HttpResponse
     */
    private $bitrixResponse;

    /**
     * @var \Slim\Http\Cookies
     */
    private $slimCookies;

    /**
     * BitrixCookieAdapterMiddleware constructor.
     * @param \Slim\Http\Cookies $slimCookies
     */
    public function __construct(\Slim\Http\Cookies $slimCookies)
    {
        $this->bitrixResponse = \Bitrix\Main\Context::getCurrent()->getResponse();

        $this->slimCookies = $slimCookies;
    }

    /**
     * @param  Request|ServerRequestInterface $request PSR7 request
     * @param  Response|ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        /** @var Response|ResponseInterface $response */
        $response = $next($request, $response);

        /** @var \Bitrix\Main\Web\Cookie[] $bitrixCookies */
        $bitrixCookies = $this->bitrixResponse->getCookies();

        /** @var \Bitrix\Main\Web\Cookie $bitrixCookie */
        foreach ($bitrixCookies as $bitrixCookie) {
            $this->slimCookies->set(
                $bitrixCookie->getName(),
                [
                    'value' => $bitrixCookie->getValue(),
                    'expires' => $bitrixCookie->getExpires(),
                    'path' => $bitrixCookie->getPath(),
                    'domain' => $bitrixCookie->getDomain(),
                    'secure' => $bitrixCookie->getSecure(),
                    'httponly' => $bitrixCookie->getHttpOnly(),
                ]
            );
        }

        return $response->withHeader('Set-Cookie', $this->slimCookies->toHeaders());
    }
}
