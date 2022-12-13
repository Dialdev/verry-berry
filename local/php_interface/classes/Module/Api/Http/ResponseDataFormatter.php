<?php

namespace Natix\Module\Api\Http;

use Natix\Module\Api\Exception\Middleware\ResponseDataFormatterException;

/**
 * @deprecated
 * Использовать теперь
 * @see Response\SuccessResponse для успешного ответа
 * @see Response\ErrorResponse для ответа с ошибками
 */
class ResponseDataFormatter
{
    const STATUS_OK = 'OK';

    const STATUS_ERROR = 'ERROR';

    private $code = 0;

    private $message = '';

    private $html = '';

    private $data = [];

    private $errors = [];

    public function __construct(array $data = [], string $message = '', array $errors = [])
    {
        $this->data = $data;

        $this->message = $message;

        $this->errors = $errors;
    }

    /**
     * @param string $status
     * @return array
     * @throws ResponseDataFormatterException
     */
    public function format(string $status = self::STATUS_OK)
    {
        $this->checkStatus($status);

        return [
            'CODE' => $this->code,
            'STATUS' => $status,
            'MESSAGE' => $this->message,
            'DATA' => $this->data,
            'ERRORS' => $this->errors,
            'HTML' => $this->html,
        ];
    }

    /**
     * @param string $status
     * @return bool
     * @throws ResponseDataFormatterException
     */
    private function checkStatus(string $status)
    {
        if (!in_array($status, [self::STATUS_OK, self::STATUS_ERROR], true)) {
            throw new ResponseDataFormatterException(
                sprintf(
                    'Передан неизвестный $status'
                )
            );
        }

        return true;
    }

    /**
     * @param int $code
     * @return ResponseDataFormatter
     */
    public function setCode(int $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Устанавливает Html в ответ
     * @param string $html
     * @return $this
     */
    public function setHtml(string $html)
    {
        $this->html = $html;
        return $this;
    }
}
