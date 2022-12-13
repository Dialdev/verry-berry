<?php

namespace Natix\Module\Api\Http\Response\Entity;

/**
 * DTO c дополнительной информацией в ответе
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class MetaEntity implements ResponseEntityInterface
{
    /** @var ServiceEntity */
    private $serviceEntity;

    /** @var PaginationEntity */
    private $paginationEntity;

    public function __construct()
    {
        $this->serviceEntity = ServiceEntity::createDefault();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if ($this->getPaginationEntity()) {
            $return['pagination'] = $this->getPaginationEntity()->toArray();
        }
        $return['service'] = $this->getServiceEntity()->toArray();
        return $return;
    }

    /**
     * @return ServiceEntity
     */
    public function getServiceEntity(): ServiceEntity
    {
        return $this->serviceEntity;
    }

    /**
     * @return PaginationEntity|null
     */
    public function getPaginationEntity()
    {
        return $this->paginationEntity;
    }
}
