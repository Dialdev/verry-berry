<?php

namespace Natix\Service\Catalog\Bouquets\Service;

use Natix\Service\Catalog\Bouquets\Collection\BerryEntityCollection;
use Natix\Service\Catalog\Bouquets\Collection\SetEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\BerryEntity;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;

/**
 * Класс призван определить доступные доп.ягоды на основе имеющихся комплектов
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BerriesCombinationService
{
    /**
     * Ищет подходящую комбинацию для переданной доп.ягоды
     * с тем же размером и упаковкой, что в текущем комплекте $currentSet
     * Доп.ягода считается доступной в комбинации, если:
     * 1) при добавлении её в коллекцию доп.ягод текущего комплекта имеется комбинация с тем же размером и упаковкой;
     * 2) при удалении её из коллекции доп.ягод текущего комплекта имеется комбинация с тем же размером и упаковкой.
     *
     * @param BerryEntity $berryEntity
     * @param SetEntity $currentSet
     * @param SetEntityCollection $setEntityCollection
     * @return SetEntity|null
     */
    public function getSetCombinationByBerry(
        BerryEntity $berryEntity,
        SetEntity $currentSet,
        SetEntityCollection $setEntityCollection
    ): ?SetEntity
    {
        $berryEntityCollection = $currentSet->isExistBerries()
            ? clone $currentSet->getBerries()
            : new BerryEntityCollection();

        if ($currentSet->isContainsBerry($berryEntity)) {
            $berryEntityCollection->remove($berryEntity->getId());
        } else {
            $berryEntityCollection->set($berryEntity->getId(), $berryEntity);
        }

        $currentBerriesSectionIds = $berryEntityCollection->getSectionIds();

        /** @var SetEntity $setEntity */
        foreach ($setEntityCollection->getIterator() as $setEntity) {
            // пропускаем комплекты с другим размером и упаковкой
            if (
                !$setEntity->isContainsSize($currentSet->getSize())
                || !$setEntity->isContainsPacking($currentSet->getPacking())
            ) {
                continue;
            }

            $berriesSectionIds = $setEntity->isExistBerries()
                ? $setEntity->getBerries()->getSectionIds()
                : [];

            if ($currentBerriesSectionIds === $berriesSectionIds) {
                return $setEntity;
            }
        }

        return null;
    }
}
