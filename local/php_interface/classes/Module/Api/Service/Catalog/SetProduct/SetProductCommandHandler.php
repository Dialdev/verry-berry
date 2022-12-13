<?php

namespace Natix\Module\Api\Service\Catalog\SetProduct;

use Bitrix\Currency\CurrencyManager;
use Natix\Module\Api\Service\Catalog\SetProduct\Command\SetProductCommand;
use Natix\Module\Api\Service\Catalog\SetProduct\Response\BerryCombination;
use Natix\Module\Api\Service\Catalog\SetProduct\Response\Combinations;
use Natix\Module\Api\Service\Catalog\SetProduct\Response\PackingCombination;
use Natix\Module\Api\Service\Catalog\SetProduct\Response\SetProductCommandHandlerResponse;
use Natix\Module\Api\Service\Catalog\SetProduct\Response\SizeCombination;
use Natix\Service\Catalog\Bouquets\Collection\SetEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\BerryEntity;
use Natix\Service\Catalog\Bouquets\Entity\PackingEntity;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;
use Natix\Service\Catalog\Bouquets\Service\BerriesCombinationService;
use Natix\Service\Catalog\Bouquets\Service\Factory\BerriesFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\PackingFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\SetFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\SizeFactory;
use Natix\Service\Catalog\Bouquets\Service\PackingCombinationService;
use Natix\Service\Catalog\Bouquets\Service\SizesCombinationService;

/**
 * Обработчик команды запроса доступной комбинации букета
 *
 * http://project.natix.ru/projects/53/tasks/4524
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetProductCommandHandler
{
    /** @var SetFactory */
    private $setFactory;
    
    /** @var SizeFactory */
    private $sizeFactory;

    /** @var BerriesFactory */
    private $berriesFactory;

    /** @var PackingFactory */
    private $packingFactory;
    
    /** @var SizesCombinationService */
    private $sizesCombinationService;

    /** @var BerriesCombinationService */
    private $berriesCombinationService;

    /** @var PackingCombinationService */
    private $packingCombinationService;
    
    public function __construct(
        SetFactory $setFactory,
        SizeFactory $sizeFactory,
        BerriesFactory $berriesFactory,
        PackingFactory $packingFactory,
        SizesCombinationService $sizesCombinationService,
        BerriesCombinationService $berriesCombinationService,
        PackingCombinationService $packingCombinationService
    ) {
        $this->setFactory = $setFactory;
        $this->sizeFactory = $sizeFactory;
        $this->berriesFactory = $berriesFactory;
        $this->packingFactory = $packingFactory;
        $this->sizesCombinationService = $sizesCombinationService;
        $this->berriesCombinationService = $berriesCombinationService;
        $this->packingCombinationService = $packingCombinationService;
    }

    /**
     * Возвращает результат выполнения обработчика команды запроса комбинации комплекта в карточке товара
     *
     * @param SetProductCommand $command
     *
     * @return SetProductCommandHandlerResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\BerriesFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\BouquetFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PackingFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\SetFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException
     */
    public function handle(SetProductCommand $command): SetProductCommandHandlerResponse
    {
        $setEntity = $this->setFactory->buildById($command->getSetId());
        $setEntityCollection = $this->setFactory->buildBySection($command->getSectionId());
        
        $combinations = new Combinations(
            $this->getSizesCombinations($setEntityCollection, $setEntity),
            $this->getBerriesCombinations($setEntityCollection, $setEntity),
            $this->getPackagingCombinations($setEntityCollection, $setEntity)
        );
        
        return new SetProductCommandHandlerResponse($setEntity, $combinations);
    }

    /**
     * Возвращает список элементов комбинаций размеров
     *
     * @param SetEntityCollection $setEntityCollection
     * @param SetEntity $setEntity
     *
     * @return array
     */
    private function getSizesCombinations(SetEntityCollection $setEntityCollection, SetEntity $setEntity): array
    {
        $sizesCombinations = [];
        $sizeEntityCollection = $this->sizeFactory->buildAllSizes();
        
        /** @var SizeEntity $sizeEntity */
        foreach ($sizeEntityCollection->getIterator() as $sizeEntity) {
            $setCombinationBySize = $this->sizesCombinationService->getSetCombinationBySize(
                $sizeEntity,
                $setEntity,
                $setEntityCollection
            );

            $priceDiff = null;
            $priceDiffFormat = null;
            if (
                $setCombinationBySize instanceof SetEntity
                && !$setCombinationBySize->isContainsSize($setEntity->getSize())
            ) {
                $priceDiff = $setCombinationBySize->getPrice()->getPriceDiscount() - $setEntity->getPrice()->getPriceDiscount();
                $priceDiffFormat = \CCurrencyLang::CurrencyFormat($priceDiff, CurrencyManager::getBaseCurrency());
            }

            $sizesCombinations[] = new SizeCombination(
                $sizeEntity->getId(),
                $sizeEntity->getName(),
                $setEntity->isContainsSize($sizeEntity),
                $setCombinationBySize instanceof SetEntity
                    ? $setCombinationBySize->getId()
                    : null,
                $setCombinationBySize instanceof SetEntity
                    ? $setCombinationBySize->getUrl()
                    : null,
                $priceDiff,
                $priceDiffFormat
            );
        }
        
        return $sizesCombinations;
    }

    /**
     * Возвращает список элементов комбинаций доп. ягод
     * 
     * @param SetEntityCollection $setEntityCollection
     * @param SetEntity $setEntity
     *
     * @return array
     */
    private function getBerriesCombinations(SetEntityCollection $setEntityCollection, SetEntity $setEntity): array
    {
        $berriesCombinations = [];
        $berryEntityCollection = $this->berriesFactory->buildBySize($setEntity->getSize());
        
        /** @var BerryEntity $berryEntity */
        foreach ($berryEntityCollection->getIterator() as $berryEntity) {
            $setCombinationByBerry = $this->berriesCombinationService->getSetCombinationByBerry(
                $berryEntity,
                $setEntity,
                $setEntityCollection
            );

            $berriesCombinations[] = new BerryCombination(
                $berryEntity->getId(),
                $berryEntity->getName(),
                $berryEntity->getCardName(),
                $setEntity->isContainsBerry($berryEntity),
                $setCombinationByBerry instanceof SetEntity
                    ? $setCombinationByBerry->getId()
                    : null,
                $setCombinationByBerry instanceof SetEntity
                    ? $setCombinationByBerry->getUrl()
                    : null,
                $berryEntity->getImage(),
                $berryEntity->getPrice()
            );
        }
        
        return $berriesCombinations;
    }

    /**
     * Возвращает список элементов комбинаций упаковок
     *
     * @param SetEntityCollection $setEntityCollection
     * @param SetEntity $setEntity
     *
     * @return array
     */
    private function getPackagingCombinations(SetEntityCollection $setEntityCollection, SetEntity $setEntity): array
    {
        $packagingCombinations = [];
        $packingEntityCollection = $this->packingFactory->buildAllPacking();
        
        /** @var PackingEntity $packingEntity */
        foreach ($packingEntityCollection->getIterator() as $packingEntity) {
            $setCombinationByPacking = $this->packingCombinationService->getSetCombinationByPacking(
                $packingEntity,
                $setEntity,
                $setEntityCollection
            );
            
            $packagingCombinations[] = new PackingCombination(
                $packingEntity->getId(),
                $packingEntity->getName(),
                $packingEntity->getCardName(),
                $setEntity->isContainsPacking($packingEntity),
                $setCombinationByPacking instanceof SetEntity
                    ? $setCombinationByPacking->getId()
                    : null,
                $setCombinationByPacking instanceof SetEntity
                    ? $setCombinationByPacking->getUrl()
                    : null,
                $packingEntity->getImage()
            );
        }
        
        return $packagingCombinations;
    }
}
