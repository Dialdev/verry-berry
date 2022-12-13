<?php

namespace Natix\Module\Api\Http\Response;

use Natix\Module\Api\Http\Response\Entity\MetaEntity;

/**
 * Класс для формирования успешного ответа API
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SuccessResponse implements ResponseInterface
{
    /** @var array */
    private $data = [];

    /** @var MetaEntity */
    private $meta;

    /** @var int */
    private $httpStatus;

    /**
     * @param array $data данные для вывода в ответе
     * @param int $httpStatus http-статус ответа API
     * @param MetaEntity $meta
     */
    public function __construct(array $data, int $httpStatus, MetaEntity $meta = null)
    {
        $this->data = $data;
        $this->httpStatus = $httpStatus;
        if ($meta === null) {
            $this->meta = new MetaEntity();
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $return = [
            'success' => true,
            'data' => $this->data,
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
