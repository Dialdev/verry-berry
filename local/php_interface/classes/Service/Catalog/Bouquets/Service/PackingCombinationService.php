<?php

namespace Natix\Service\Catalog\Bouquets\Service;

use Natix\Service\Catalog\Bouquets\Collection\SetEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\PackingEntity;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;

/**
 * Класс призван определять доступные упаковки на основе имеющихся комплектов
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PackingCombinationService
{
    /**
     * Ищет подходящую комбинацию для переданной упаковки среди доступных комплектов
     * с тем же набором доп.ягод и тем же размером, что в текущем комплекте
     *
     * @param PackingEntity $packingEntity
     * @param SetEntity $currentSet
     * @param SetEntityCollection $setEntityCollection
     * @return SetEntity|null
     */
    public function getSetCombinationByPacking(
        PackingEntity $packingEntity,
        SetEntity $currentSet,
        SetEntityCollection $setEntityCollection
    ): ?SetEntity
    {
        $currentBerriesSectionIds = $currentSet->isExistBerries()
            ? $currentSet->getBerries()->getSectionIds()
            : [];

        /** @var SetEntity $setEntity */
        foreach ($setEntityCollection->getIterator() as $setEntity) {
            // пропускаем комплекты с другими размерами и упаковкой
            if (
                !$setEntity->isContainsPacking($packingEntity)
                || !$setEntity->isContainsSize($currentSet->getSize())
            ) {
                continue;
            }

            $berriesSectionIds = $setEntity->getBerries() !== null
                ? $setEntity->getBerries()->getSectionIds()
                : [];

            if ($currentBerriesSectionIds === $berriesSectionIds) {
                return $setEntity;
            }
        }

        return null;
    }
}
