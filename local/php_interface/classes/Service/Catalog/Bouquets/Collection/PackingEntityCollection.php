<?php

namespace Natix\Service\Catalog\Bouquets\Collection;

use Natix\Data\Collection\Collection;
use Natix\Service\Catalog\Bouquets\Entity\PackingEntity;

/**
 * Коллекция сущностей упаковок
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PackingEntityCollection extends Collection
{
    /**
     * Преобразует коллекцию упаковок в массив
     * @param PackingEntityCollection $collection
     * @return array
     */
    public static function toState(self $collection): array
    {
        $result = [];

        /** @var PackingEntity $packingEntity */
        foreach ($collection as $packingEntity) {
            $result[$packingEntity->getId()] = PackingEntity::toState($packingEntity);
        }

        return $result;
    }
}
