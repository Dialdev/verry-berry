<?php

namespace Natix\Service\Catalog\Bouquets\Collection;

use Natix\Data\Collection\Collection;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;

/**
 * Коллекция комплектов
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetEntityCollection extends Collection
{
    /**
     * Преобразует коллекцию комплектов в массив
     * @param SetEntityCollection $collection
     * @return array
     */
    public static function toState(self $collection): array
    {
        $result = [];

        /** @var SetEntity $setEntity */
        foreach ($collection as $setEntity) {
            $result[$setEntity->getId()] = SetEntity::toState($setEntity);
        }

        return $result;
    }
}
