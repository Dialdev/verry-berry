<?php

namespace Natix\Service\Catalog\Bouquets\Service;

use Natix\Service\Catalog\Bouquets\Collection\SetEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;

/**
 * Класс призван определять доступные размеры на основве имеющихся комплектов
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SizesCombinationService
{
    /**
     * Ищет подходящую комбинацию для переданного размера среди доступных комплектов
     * с тем же набором ягод и упаковкой, что в текущем комплекте $currentSet
     *
     * @param SizeEntity $sizeEntity
     * @param SetEntity $currentSet
     * @param SetEntityCollection $setEntityCollection
     * @return SetEntity|null
     */
    public function getSetCombinationBySize(
        SizeEntity $sizeEntity,
        SetEntity $currentSet,
        SetEntityCollection $setEntityCollection
    ): ?SetEntity
    {
        $currentBerriesSectionIds = $currentSet->isExistBerries()
            ? $currentSet->getBerries()->getSectionIds()
            : [];

        /** @var SetEntity $setEntity */
        foreach ($setEntityCollection->getIterator() as $setEntity) {
            // пропускаем комплекты с другими размерами и другой упаковкой
            if (
                !$setEntity->isContainsSize($sizeEntity)
                || !$setEntity->isContainsPacking($currentSet->getPacking())
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
