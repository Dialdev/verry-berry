<?php

namespace Natix\Module\Api\Http\Response;

use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\Entity\MetaEntity;

/**
 * Класс для формирования ответа API при ошибке
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ErrorResponse implements ResponseInterface
{
    /** @var ErrorEntityCollection */
    private $errors;

    /** @var MetaEntity */
    private $meta;

    /** @var int */
    private $httpStatus;

    /**
     * @param ErrorEntityCollection $errors коллекция ошибок
     * @param int $httpStatus http-статус ответа API
     * @param MetaEntity $meta мета данные
     */
    public function __construct(ErrorEntityCollection $errors, int $httpStatus, MetaEntity $meta = null)
    {
        $this->meta = $meta;
        $this->httpStatus = $httpStatus;
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $return = [
            'success' => false,
            'errors' => $this->errors->toArray(),
        ];

        if ($this->meta) {
            $return['meta'] = $this->meta->toArray();
        }

        return $return;
    }

    /**
     * @return int
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
