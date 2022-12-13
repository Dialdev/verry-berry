<?php

namespace Natix\Module\Api\Middleware;

use Natix\Helpers\EnvironmentHelper;
use Psr\Log\LoggerInterface;

/**
 * Middleware логирует успешные ответы API
 * Логирование управляется опцией api_backend.log.enable_success_response в конфиге
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class WriteResponseToLogMiddleware
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param callable $next Next middleware
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        /** @var \Psr\Http\Message\ResponseInterface $nextResponse */
        $nextResponse = $next($request, $response);

        $nextResponse->getBody()->rewind();

        // {{{
        try {
            $apiBackendSettings = EnvironmentHelper::getParam('api_backend') ?? null;

            $logSuccessResponseEnableByFlag = $apiBackendSettings['log']['enable_success_response'] ?? false;

            $logSuccessResponseEnableByMask = true;

            $logSuccessResponseMask = $apiBackendSettings['log']['enable_success_response_mask'] ?? [];

            $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? null;

            if (!empty($logSuccessResponseMask)) {
                $logSuccessResponseEnableByMask = false;

                foreach ($logSuccessResponseMask as $pattern) {
                    if (preg_match(sprintf('~%s~', $pattern), $urlPath)) {
                        $logSuccessResponseEnableByMask = true;

                        break;
                    }
                }
            }

            if (
                $logSuccessResponseEnableByFlag === true
                && (
                    !empty($logSuccessResponseMask)
                        ? ($logSuccessResponseEnableByMask === true)
                        : true
                )
            ) {
                /** @var LoggerInterface $logger */
                $logger = \Natix::$container->get(LoggerInterface::class);

                $logger->debug(
                    sprintf(
                        'Успешный ответ API бэкенда: "%s". (данные $_GET = "%s", $_POST = "%s", $bodyString = "%s")',
                        $nextResponse->getBody()->getContents(),
                        json_encode($_GET),
                        json_encode($_POST),
                        json_encode(file_get_contents('php://input'))
                    ),
                    [
                        'func_name' => __METHOD__,
                        'service' => 'api_backend',
                        'sub_service' => $urlPath,
                        'request_method' => mb_strtolower($_SERVER['REQUEST_METHOD']),
                    ]
                );
            }
        } catch (\Throwable $e) {
        }
        // }}}

        return $nextResponse;
    }
}
