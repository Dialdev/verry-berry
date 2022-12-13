<?php

namespace Natix\Service\Catalog\Bouquets\Collection;

use Natix\Data\Collection\Collection;
use Natix\Service\Catalog\Bouquets\Entity\BerryEntity;

/**
 * Коллекция объектов сущностей дополнительных ягод для букета
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BerryEntityCollection extends Collection
{
    /**
     * Преобразует коллекцию ягод в массив
     * @param BerryEntityCollection $collection
     * @return array
     */
    public static function toState(self $collection): array
    {
        $result = [];

        /** @var BerryEntity $berryEntity */
        foreach ($collection as $berryEntity) {
            $result[$berryEntity->getId()] = BerryEntity::toState($berryEntity);
        }

        return $result;
    }

    /**
     * Возвращает id разделов, в которых лежат дополнительные ягоды, содержащиеся в комлекте
     * @return array
     */
    public function getSectionIds(): array
    {
        $sectionIds = [];

        /** @var BerryEntity $berryEntity */
        foreach ($this->getIterator() as $berryEntity) {
            if ($berryEntity->getSectionId() > 0) {
                $sectionIds[] = $berryEntity->getSectionId();
            }
        }
        sort($sectionIds);

        return $sectionIds;
    }
}
