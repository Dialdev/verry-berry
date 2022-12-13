<?php

namespace Natix\Component;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Context;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Service\Catalog\Bouquets\Collection\BerryEntityCollection;
use Natix\Service\Catalog\Bouquets\Collection\PackingEntityCollection;
use Natix\Service\Catalog\Bouquets\Collection\SetEntityCollection;
use Natix\Service\Catalog\Bouquets\Collection\SizeEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\BerryEntity;
use Natix\Service\Catalog\Bouquets\Entity\PackingEntity;
use Natix\Service\Catalog\Bouquets\Entity\PriceEntity;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;
use Natix\Service\Catalog\Bouquets\Exception\BerriesFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\BouquetFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PackingFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SetFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException;
use Natix\Service\Catalog\Bouquets\Service\BerriesCombinationService;
use Natix\Service\Catalog\Bouquets\Service\Factory\BerriesFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\BouquetFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\PackingFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\SetFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\SizeFactory;
use Natix\Service\Catalog\Bouquets\Service\PackingCombinationService;
use Natix\Service\Catalog\Bouquets\Service\SizesCombinationService;
use Psr\Log\LoggerInterface;

/**
 * Компонент карточки товара с букетами-комплектами
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogElementSet extends CommonComponent
{
    /**
     * @var array
     */
    protected $needModules = [
        'iblock',
        'catalog',
    ];

    /** @var LoggerInterface */
    private $logger;

    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @var SetFactory
     */
    private $setFactory;

    /**
     * @var BouquetFactory
     */
    private $bouquetFactory;

    /**
     * @var SizeFactory
     */
    private $sizeFactory;

    /**
     * @var BerriesFactory
     */
    private $berriesFactory;

    /**
     * @var PackingFactory
     */
    private $packingFactory;

    /**
     * @var SizesCombinationService
     */
    private $sizesCombinationService;

    /**
     * @var BerriesCombinationService
     */
    private $berriesCombinationService;

    /**
     * @var PackingCombinationService
     */
    private $packingCombinationService;

    /**
     * @var SetEntity
     */
    private $set;

    /**
     * @var SetEntityCollection
     */
    private $setEntityCollection;

    /**
     * @var SizeEntityCollection
     */
    private $sizeEntityCollection;

    /**
     * @var BerryEntityCollection
     */
    private $berryEntityCollection;

    /**
     * @var PackingEntityCollection
     */
    private $packingEntityCollection;

    /**
     * @param null $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->logger = $this->getContainer()->get(LoggerInterface::class);
        $this->iblockFinder = $this->getContainer()->get(IblockFinder::class);
        $this->setFactory = $this->getContainer()->get(SetFactory::class);
        $this->sizeFactory = $this->getContainer()->get(SetFactory::class);
        $this->bouquetFactory = $this->getContainer()->get(BouquetFactory::class);
        $this->sizeFactory = $this->getContainer()->get(SizeFactory::class);
        $this->berriesFactory = $this->getContainer()->get(BerriesFactory::class);
        $this->sizesCombinationService = $this->getContainer()->get(SizesCombinationService::class);
        $this->berriesCombinationService = $this->getContainer()->get(BerriesCombinationService::class);
        $this->packingCombinationService = $this->getContainer()->get(PackingCombinationService::class);
        $this->packingFactory = $this->getContainer()->get(PackingFactory::class);
    }

    protected function configurate(): void
    {
        $this->arParams['CACHE_TYPE'] = 'N';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        $this->arParams['ELEMENT_CODE'] = $this->request->get('ELEMENT_CODE');

        if (strlen($this->arParams['ELEMENT_CODE']) <= 0) {
            $this->process404();
        }
    }

    /**
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SizeFactoryException
     * @throws SetFactoryException
     */
    protected function executeMain(): void
    {
        $this->set = $this->setFactory->buildByCode($this->arParams['ELEMENT_CODE']);

        if (!$this->set->isInSection()) {
            throw new \RuntimeException('Ошибка: комплект должен находиться внутри раздела-букета!');
        }

        if (!$this->set->isExistBouquet()) {
            throw new \RuntimeException('Ошибка: в комплекте отсутствует основа букета.');
        }

        $this->arResult['set'] = SetEntity::toState($this->set);

        $this->setEntityCollection = $this->setFactory->buildBySection($this->set->getSectionId());

        $this->prepareSectionData();

        $this->arResult['product'] = $this->getProduct();
        //$this->prepareProductTypeSet();
    }

    /**
     * Возвращает информацию о товаре
     *
     * @return array
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException
     * @throws \Natix\Service\Tools\Catalog\Exception\ProductTypeCheckerException
     */
    protected function getProduct(): array
    {
        $iterator = \CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->iblockFinder->catalog(),
                'CODE' => $this->arParams['ELEMENT_CODE'],
            ],
            false,
            ['nTopCount' => 1],
            [
                'ID',
                'NAME',
                'IBLOCK_SECTION_ID',
                'PREVIEW_PICTURE',
                'PREVIEW_TEXT',
                'DETAIL_PICTURE',
                'DETAIL_TEXT',
                'CATALOG_GROUP_1',
            ]
        );

        $result = [];

        if ($item = $iterator->Fetch()) {
            $productId = (int)$item['ID'];

            $item['PROPERTIES'] = $this->getProperties($productId, $this->iblockFinder->catalog());

            $result = $item;
        }

        return $result;
    }

    /**
     * Возвращает свойства товара
     *
     * @param int $productId
     * @param int $iblockId
     *
     * @return array
     */
    protected function getProperties(int $productId, int $iblockId): array
    {
        $properties = [];

        $propertyIterator = \CIBlockElement::GetProperty(
            $iblockId,
            $productId,
            [
                'SORT' => 'ASC',
                'ID' => 'ASC'
            ],
            [
                'ACTIVE' => 'Y',
            ]
        );

        while ($property = $propertyIterator->Fetch()) {
            if ($property['MULTIPLE'] === 'Y') {

                $iterator = \CIBlockElement::GetProperty(
                    $iblockId,
                    $productId,
                    [
                        'SORT' => 'ASC',
                        'ID' => 'ASC'
                    ],
                    ['CODE' => $property['CODE']]
                );

                while ($item = $iterator->GetNext()) {
                    $property['VALUES'][] = $item['VALUE'];
                }
            }

            $code = trim($property['CODE']);
            $properties[$code] = $property;
        }

        return $properties;
    }

    /**
     * Подготавливает данные комплекта
     * Комплект - это букет, который содержит дополнительные ягоды и упаковку.
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SizeFactoryException
     */
    protected function prepareProductTypeSet(): void
    {
        $this->prepareSizesCombination();

        $this->prepareBerriesCombination();

        $this->preparePackingCombination();
    }

    /**
     * Подготавливает размеры для вывода в карточке товара
     * @throws FinderEmptyValueException
     */
    protected function prepareSizesCombination(): void
    {
        $this->sizeEntityCollection = $this->sizeFactory->buildAllSizes();
        $this->arResult['sizes'] = SizeEntityCollection::toState($this->sizeEntityCollection);

        /** @var SizeEntity $sizeEntity */
        foreach ($this->sizeEntityCollection->getIterator() as $sizeEntity) {
            $setCombinationBySize = $this->sizesCombinationService->getSetCombinationBySize(
                $sizeEntity,
                $this->set,
                $this->setEntityCollection
            );

            $id = $sizeEntity->getId();

            $this->arResult['sizes'][$id]['url'] = $setCombinationBySize instanceof SetEntity
                ? $setCombinationBySize->getUrl()
                : null;

            if (
                $setCombinationBySize instanceof SetEntity
                && !$setCombinationBySize->isContainsSize($this->set->getSize())
            ) {
                $priceDiff = $setCombinationBySize->getPrice()->getPriceDiscount()
                    - $this->set->getPrice()->getPriceDiscount();

                $this->arResult['sizes'][$id]['price_diff'] = $priceDiff;
                $this->arResult['sizes'][$id]['price_diff_format'] = \CCurrencyLang::CurrencyFormat(
                    $priceDiff,
                    CurrencyManager::getBaseCurrency()
                );
            }

            $this->arResult['sizes'][$id]['active'] = $this->set->isContainsSize($sizeEntity);
        }
    }

    /**
     * Подготавливает доп.ягоды для вывода в карточке товара
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PriceFactoryException
     * @throws SizeFactoryException
     */
    protected function prepareBerriesCombination(): void
    {
        $this->berryEntityCollection = $this->berriesFactory->buildBySize($this->set->getBouquet()->getSize());
        $this->arResult['berries'] = BerryEntityCollection::toState($this->berryEntityCollection);

        /** @var BerryEntity $berryEntity */
        foreach ($this->berryEntityCollection->getIterator() as $berryEntity) {
            $setCombinationByBerry = $this->berriesCombinationService->getSetCombinationByBerry(
                $berryEntity,
                $this->set,
                $this->setEntityCollection
            );

            $id = $berryEntity->getId();

            $this->arResult['berries'][$id]['url'] = $setCombinationByBerry instanceof SetEntity
                ? $setCombinationByBerry->getUrl()
                : null;

            $this->arResult['berries'][$id]['active'] = $this->set->isContainsBerry($berryEntity);
        }
    }

    /**
     * Подготавливает упаковки для вывода в карточке товара
     */
    protected function preparePackingCombination(): void
    {
        $this->packingEntityCollection = $this->packingFactory->buildAllPacking();
        $this->arResult['packaging'] = PackingEntityCollection::toState($this->packingEntityCollection);

        /** @var PackingEntity $packingEntity */
        foreach ($this->packingEntityCollection->getIterator() as $packingEntity) {
            $setCombinationByPacking = $this->packingCombinationService->getSetCombinationByPacking(
                $packingEntity,
                $this->set,
                $this->setEntityCollection
            );

            $id = $packingEntity->getId();

            $this->arResult['packaging'][$id]['url'] = $setCombinationByPacking instanceof SetEntity
                ? $setCombinationByPacking->getUrl()
                : null;

            $this->arResult['packaging'][$id]['active'] = $this->set->isContainsPacking($packingEntity);
        }
    }

    /**
     * Подготавливает данные раздела
     * @throws \Exception
     */
    protected function prepareSectionData(): void
    {
        if (!$this->arResult['set']['section_id']) {
            return;
        }

        $section = SectionTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=ID' => $this->arResult['set']['section_id'],
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!isset($section['ID']) || $section['ID'] <= 0) {
            throw new \RuntimeException(sprintf(
                'По ID "%s" не найден раздел в каталоге товаров',
                $this->arResult['product']['IBLOCK_SECTION_ID']
            ));
        }

        $this->arResult['set']['section'] = $section;
    }

    public function returnDatas(): void
    {
        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        $APPLICATION->SetTitle($this->arResult['set']['card_name']);

        if ($this->arResult['set']['section']) {
            $APPLICATION->AddChainItem(
                $this->arResult['set']['section']['NAME'],
                sprintf('/catalog/%s/', $this->arResult['set']['section']['CODE'])
            );
        }

        $APPLICATION->AddChainItem($this->arResult['set']['card_name']);

        parent::returnDatas();
    }

    protected function process404(): void
    {
        Tools::process404('', true, true, true);
    }

    /**
     * Обработка ошибок в карточке товара
     *
     * @param \Exception $exception
     * @param null $notifier
     */
    protected function catchException(\Exception $exception, $notifier = null): void
    {
        $currentPage = Context::getCurrent()->getRequest()->getRequestedPage();

        $this->logger->error(
            sprintf(
                'Ошибка в карточке товара на странице %s: %s',
                $currentPage,
                $exception->getMessage()
            ),
            ['func_name' => __METHOD__]
        );
    }
}
