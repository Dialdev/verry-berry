<?php

namespace Natix\Service\Catalog\Bouquets\Collection;

use Natix\Data\Collection\Collection;
use Natix\Service\Catalog\Bouquets\Entity\ImageEntity;

/**
 * Коллекция сущностей картинок
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ImageEntityCollection extends Collection
{
    /**
     * Преобразует коллекцию картинок в массив
     * @param ImageEntityCollection $collection
     * @return array
     */
    public static function toState(self $collection): array
    {
        $result = [];

        /** @var ImageEntity $imageEntity */
        foreach ($collection as $imageEntity) {
            $result[$imageEntity->getId()] = ImageEntity::toState($imageEntity);
        }

        return $result;
    }
}
