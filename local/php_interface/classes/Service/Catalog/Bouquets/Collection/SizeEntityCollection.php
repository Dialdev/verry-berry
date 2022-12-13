<?php

namespace Natix\Service\Catalog\Bouquets\Collection;

use Natix\Data\Collection\Collection;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;

/**
 * Коллекция объектов с размерами
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SizeEntityCollection extends Collection
{
    /**
     * Преобразует коллекцию размеров в массив
     * @param SizeEntityCollection $collection
     * @return array
     */
    public static function toState(self $collection): array
    {
        $result = [];

        /** @var SizeEntity $sizeEntity */
        foreach ($collection as $sizeEntity) {
            $result[$sizeEntity->getId()] = SizeEntity::toState($sizeEntity);
        }

        return $result;
    }
}
