<?php

namespace Natix\Module\Api\Handlers;

use Natix\Module\Api\Http\ResponseDataFormatter;
use Psr\Log\LoggerInterface;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Error extends \Slim\Handlers\Error
{
    /**
     * Перегрузим слимовский хэндлер, чтобы ответ соответствовал нашему формату
     *
     * @param \Exception $exception
     * @return string
     * @throws \Natix\Module\Api\Exception\Middleware\ResponseDataFormatterException
     */
    protected function renderJsonErrorMessage(\Exception $exception)
    {
        $errorCode = $exception->getCode();

        $errorMessage = $exception->getMessage();

        $errorTraceAsString = $exception->getTraceAsString();

        $error = [];

        $error['EXCEPTION'] = [];

        do {
            if ($this->displayErrorDetails) {
                $exceptionItem = [
                    'type' => get_class($exception),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                ];
            } else {
                $exceptionItem = [
                    'code' => $exception->getCode(),
                ];
            }

            $error['EXCEPTION'][] = $exceptionItem;
        } while ($exception = $exception->getPrevious());

        // {{{
        try {
            /** @var LoggerInterface $logger */
            $logger = \Natix::$container->get(LoggerInterface::class);

            $logger->debug(
                sprintf(
                    'Неуспешный ответ API бэкенда: %s. Трейс: "%s" (данные $_GET = "%s", $_POST = "%s", $_SERVER = "%s", $bodyString = "%s")',
                    $errorMessage,
                    $errorTraceAsString,
                    json_encode($_GET),
                    json_encode($_POST),
                    json_encode($_SERVER),
                    json_encode(file_get_contents('php://input'))
                ),
                [
                    'func_name' => __METHOD__,
                    'service' => 'api_backend',
                    'sub_service' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? null,
                    'request_method' => mb_strtolower($_SERVER['REQUEST_METHOD']),
                ]
            );
        } catch (\Throwable $e) {
        }
        // }}}

        $result = (new ResponseDataFormatter([], $errorMessage, $error))
            ->setCode($errorCode)
            ->format(ResponseDataFormatter::STATUS_ERROR);

        return json_encode($result);
    }
}
