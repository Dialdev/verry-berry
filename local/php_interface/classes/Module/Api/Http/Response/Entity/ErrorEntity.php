<?php

namespace Natix\Module\Api\Http\Response\Entity;

/**
 * DTO для описания ошибки
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ErrorEntity implements ResponseEntityInterface
{
    /** @var string */
    private $message;

    /** @var string|null */
    private $code;

    /**
     *
     * @param string $message текст ошибки.
     * @param string $code уникальный GUID ошибки
     */
    public function __construct(string $message, string $code = null)
    {
        $this->message = $message;
        if ($code !== null) {
            $this->code = $code;
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    public function toArray(): array
    {
        return $this->getCode()
            ? ['message' => $this->getMessage(), 'code' => $this->getCode()]
            : ['message' => $this->getMessage()];
    }
}
