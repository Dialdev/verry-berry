<?php

namespace Natix\Service\Catalog\Bouquets\Service\Factory;

use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Data\Bitrix\Finder\Iblock\IblockPropertyFinder;
use Natix\Entity\Orm\BitrixCatalogProductSetsTable;
use Natix\Entity\Orm\Iblock\MultiplePropertyElementTable;
use Natix\Entity\Orm\CatalogProductTable;
use Natix\Service\Catalog\Bouquets\Collection\SetEntityCollection;
use Natix\Service\Catalog\Bouquets\Dto\SetQueryParamsDto;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;
use Natix\Service\Catalog\Bouquets\Exception\BerriesFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\BouquetFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PackingFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SetFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException;
use Natix\UI\PageNavigation;

/**
 * Фабрика для создания объекта сущности комплекта
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetFactory
{
    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @var PriceTypeFinder
     */
    private $priceTypeFinder;

    /**
     * @var IblockPropertyFinder
     */
    private $iblockPropertyFinder;

    /**
     * @var SizeFactory
     */
    private $sizeFactory;

    /**
     * @var BouquetFactory
     */
    private $bouquetFactory;

    /**
     * @var BerriesFactory
     */
    private $berriesFactory;

    /**
     * @var PackingFactory
     */
    private $packingFactory;

    /**
     * @var PriceFactory
     */
    private $priceFactory;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var array
     */
    private static $sectionNameMap = [];

    /**
     * @var array
     */
    private static $setProductIds = null;

    /**
     * @param IblockFinder         $iblockFinder
     * @param PriceTypeFinder      $priceTypeFinder
     * @param IblockPropertyFinder $iblockPropertyFinder
     * @param SizeFactory          $sizeFactory
     * @param BouquetFactory       $bouquetFactory
     * @param BerriesFactory       $berriesFactory
     * @param PackingFactory       $packingFactory
     * @param PriceFactory         $priceFactory
     * @param ImageFactory         $imageFactory
     * @throws FinderEmptyValueException
     */
    public function __construct(
        IblockFinder $iblockFinder,
        PriceTypeFinder $priceTypeFinder,
        IblockPropertyFinder $iblockPropertyFinder,
        SizeFactory $sizeFactory,
        BouquetFactory $bouquetFactory,
        BerriesFactory $berriesFactory,
        PackingFactory $packingFactory,
        PriceFactory $priceFactory,
        ImageFactory $imageFactory
    ) {
        $this->iblockFinder = $iblockFinder;
        $this->priceTypeFinder = $priceTypeFinder;
        $this->iblockPropertyFinder = $iblockPropertyFinder;
        $this->sizeFactory = $sizeFactory;
        $this->bouquetFactory = $bouquetFactory;
        $this->berriesFactory = $berriesFactory;
        $this->packingFactory = $packingFactory;
        $this->priceFactory = $priceFactory;
        $this->imageFactory = $imageFactory;

        $this->prepareSetProductIds();
    }

    /**
     * Возвращает сущность комплекта по его id
     *
     * @param int $id
     * @return SetEntity
     * @throws ArgumentException
     * @throws FinderEmptyValueException
     * @throws SetFactoryException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SizeFactoryException
     */
    public function buildById(int $id): SetEntity
    {
        if ($id <= 0) {
            throw new SetFactoryException('$id должен быть больше 0');
        }

        $query = $this->getBaseQuery()
            ->addFilter('=ID', $id)
            ->setLimit(1);

        $set = $query->exec()->fetch();

        if (!$set) {
            throw new SetFactoryException(sprintf('Комплект с id "%s" не найден', $id));
        }

        return $this->buildByRow($set, true);
    }

    /**
     * Возвращает сущность комплекта по его символьному коду
     *
     * @param string $code
     * @return SetEntity
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SetFactoryException
     * @throws SizeFactoryException
     */
    public function buildByCode(string $code): SetEntity
    {
        if (strlen($code) <= 0) {
            throw new BouquetFactoryException('$code должен быть не пустой строкой');
        }

        $row = ElementTable::query()
            ->setSelect(['ID'])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=CODE'      => $code,
            ])
            ->setLimit(1)
            ->setCacheTtl(3600)
            ->exec()
            ->fetch();

        if (!$row || !isset($row['ID']) || (int)$row['ID'] <= 0) {
            throw new SetFactoryException(sprintf('Не найден id комплекта по коду "%s"', $code));
        }

        return $this->buildById((int)$row['ID']);
    }

    /**
     * Возвращает коллекцию комплектов, которые находятся в переданном разделе
     *
     * @param int $sectionId
     * @return SetEntityCollection
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SetFactoryException
     * @throws SizeFactoryException
     */
    public function buildBySection(int $sectionId): SetEntityCollection
    {
        if ($sectionId <= 0) {
            throw new SetFactoryException('$sectionId должен быть больше 0');
        }

        $collection = new SetEntityCollection();

        $query = $this->getBaseQuery()->addFilter('=IBLOCK_SECTION_ID', $sectionId);

        $iterator = $query->exec();

        while ($set = $iterator->fetch()) {
            $setEntity = $this->buildByRow($set);
            $collection->set($setEntity->getId(), $setEntity);
        }

        return $collection;
    }

    /**
     * Возвращает коллекцию комплектов по переданным параметрам запроса
     *
     * @param SetQueryParamsDto   $dto
     * @param PageNavigation|null $pageNavigation
     * @return SetEntityCollection
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SetFactoryException
     * @throws SizeFactoryException
     */
    public function buildByParams(SetQueryParamsDto $dto, ?PageNavigation $pageNavigation): SetEntityCollection
    {
        $query = $this->getBaseQuery();

        if (is_array($dto->getFilter())) {
            foreach ($dto->getFilter() as $field => $value) {
                $query->addFilter($field, $value);
            }
        }

        if ($dto->getSortField() !== null && $dto->getSortOrder() !== null) {
            $query->setOrder([$dto->getSortField() => $dto->getSortOrder()]);
        }

        if ($dto->getLimit() > 0) {
            $query->setLimit($dto->getLimit());
        }

        if ($dto->getOffset() > 0) {
            $query->setOffset($dto->getOffset());
        }

        if ($pageNavigation !== null) {
            $query->countTotal(true);
        }

        $collection = new SetEntityCollection();

        $iterator = $query->exec();

        if ($pageNavigation !== null) {
            $pageNavigation->setRecordCount($iterator->getCount());
        }

        while ($set = $iterator->fetch()) {
            $setEntity = $this->buildByRow($set);
            $collection->set($setEntity->getId(), $setEntity);
        }

        return $collection;
    }

    /**
     * Создаёт сущность комплекта из переданных данных
     *
     * @param array $set
     * @param bool  $isPrepareDopImages - подготавливать ли доп.картинки у комплекта?
     * @return SetEntity
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws BouquetFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     * @throws PriceFactoryException
     * @throws SetFactoryException
     * @throws SizeFactoryException
     */
    public function buildByRow(array $set, bool $isPrepareDopImages = false): SetEntity
    {
        if (empty($set)) {
            throw new SetFactoryException('$set должен быть не пустым массивом');
        }

        if (
            $set['IBLOCK_SECTION_ID'] > 0
            && !isset(self::$sectionNameMap[$set['IBLOCK_SECTION_ID']])
        ) {
            $section = SectionTable::getRow([
                'select' => ['NAME'],
                'filter' => [
                    '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                    '=ID'        => $set['IBLOCK_SECTION_ID'],
                ],
                'cache'  => ['ttl' => 3600],
            ]);

            self::$sectionNameMap[$set['IBLOCK_SECTION_ID']] = $section['NAME'];
        }

        if ($set['IBLOCK_SECTION_ID']) {
            $sections = \CIBlockSection::GetNavChain($this->iblockFinder->catalog(), $set['IBLOCK_SECTION_ID']);

            $sectionsTextChain = '';

            while ($section = $sections->GetNext())
                $sectionsTextChain .= trim($section['NAME']).'/';

            $sectionsTextChain = trim($sectionsTextChain, '/');
        }

        $setProducts = self::$setProductIds[$set['ID']];

        $dopImageIds = $isPrepareDopImages ? $this->getDopImageIds($set['ID']) : [];

        return new SetEntity(
            (int)$set['ID'],
            $set['NAME'],
            self::$sectionNameMap[$set['IBLOCK_SECTION_ID']] ?? $set['NAME'],
            $set['CODE'],
            $set['PREVIEW_PICTURE'] > 0
                ? $this->imageFactory->build((int)$set['PREVIEW_PICTURE'], true, 131, 98)
                : null,
            $this->priceFactory->build($this->iblockFinder->catalog(), (int)$set['ID'], (float)$set['PRICE']),
            $set['PROPERTY_SIZE'] ? $this->sizeFactory->buildById($set['PROPERTY_SIZE']) : null,
            $setProducts['bouquetId'] ? $this->bouquetFactory->buildById($setProducts['bouquetId']) : null,
            !empty($setProducts['berryIds']) ? $this->berriesFactory->buildByIds($setProducts['berryIds']) : null,
            $setProducts['packingId'] ? $this->packingFactory->buildById($setProducts['packingId']) : null,
            !empty($dopImageIds)
                ? $this->imageFactory->buildByIds($dopImageIds, false, 131, 98)
                : null,
            $set['PROPERTY_ARTICUL'] ?? null,
            $set['IBLOCK_SECTION_ID'] ? (int)$set['IBLOCK_SECTION_ID'] : null,
            $sectionsTextChain ?? null
        );
    }

    /**
     * Возвращает базовый объект запроса для комплектов
     *
     * @return Query
     * @throws ArgumentException
     * @throws FinderEmptyValueException
     */
    private function getBaseQuery(): Query
    {
        return CatalogProductTable::query()
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
                'PREVIEW_PICTURE',
                'IBLOCK_SECTION_ID',
                'PROPERTY_ARTICUL',
                'PROPERTY_SIZE',
                'PRODUCT_TYPE' => 'PRODUCT.TYPE',
                'PRICE'        => 'PRICE_TABLE.PRICE',
            ])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
            ])
            ->registerRuntimeField('PRODUCT', [
                'data_type' => ProductTable::class,
                'reference' => [
                    '=this.ID' => 'ref.ID',
                ],
                'join_type' => 'inner',
            ])
            ->registerRuntimeField('PRICE_TABLE', [
                'data_type' => PriceTable::class,
                'reference' => [
                    '=this.ID'              => 'ref.PRODUCT_ID',
                    '=ref.CATALOG_GROUP_ID' => new SqlExpression('?i', $this->priceTypeFinder->base()),
                ],
                'join_type' => 'left',
            ]);
    }

    /**
     * Возвращает идентификаторы товаров, входящих в комплект, которые сгруппированы по инфоблокам
     *
     * @param int $setId
     * @return array
     * @throws FinderEmptyValueException
     */
    private function getSetProductIds(int $setId): array
    {
        $result = [
            'bouquetId' => null,
            'berryIds'  => [],
            'packingId' => null,
        ];

        $setIterator = BitrixCatalogProductSetsTable::query()
            ->setSelect([
                'ITEM_ID',
                'IBLOCK_ID' => 'ELEMENT.IBLOCK_ID',
            ])
            ->setFilter([
                '=OWNER_ID' => $setId,
                '!=ITEM_ID' => $setId,
            ])
            ->registerRuntimeField('ELEMENT', [
                'data_type' => ElementTable::class,
                'reference' => [
                    '=this.ITEM_ID' => 'ref.ID',
                ],
                'join_type' => 'inner',
            ])
            ->exec();

        while ($setItem = $setIterator->fetch()) {
            if ((int)$setItem['IBLOCK_ID'] === $this->iblockFinder->bouquet()) {
                $result['bouquetId'] = (int)$setItem['ITEM_ID'];
            }

            if ((int)$setItem['IBLOCK_ID'] === $this->iblockFinder->berries()) {
                $result['berryIds'][] = (int)$setItem['ITEM_ID'];
            }

            if ((int)$setItem['IBLOCK_ID'] === $this->iblockFinder->packing()) {
                $result['packingId'] = (int)$setItem['ITEM_ID'];
            }
        }

        return $result;
    }

    /**
     * Возвращает идентификаторы дополнительных картинок комплекта
     *
     * @param int $setId
     * @return array
     * @throws FinderEmptyValueException
     * @throws ArgumentException
     */
    private function getDopImageIds(int $setId): array
    {
        $multiEntity = MultiplePropertyElementTable::createEntity($this->iblockFinder->catalog());
        $dopImagesIterator = $multiEntity->getDataClass()::query()
            ->setSelect(['VALUE'])
            ->setFilter([
                '=IBLOCK_ELEMENT_ID'  => $setId,
                '=IBLOCK_PROPERTY_ID' => $this->iblockPropertyFinder->catalogDopImagesId(),
            ])->exec();

        $dopImageIds = [];

        while ($item = $dopImagesIterator->fetch()) {
            if ((int)$item['VALUE'] <= 0) {
                continue;
            }
            $dopImageIds[] = (int)$item['VALUE'];
        }

        return $dopImageIds;
    }

    /**
     * Подготавливает массив ID товаров, входящих в комплекты,
     * разбитые по основе букета, ягодам и упаковке
     *
     * @throws FinderEmptyValueException
     */
    private function prepareSetProductIds()
    {
        if (self::$setProductIds === null) {
            $result = [];

            Loader::includeModule('iblock');

            $setIterator = BitrixCatalogProductSetsTable::query()
                ->setSelect([
                    'ITEM_ID',
                    'OWNER_ID',
                    'ID',
                    'IBLOCK_ID' => 'ELEMENT.IBLOCK_ID',
                ])
                ->setFilter([
                    '!=SET_ID' => 0,
                ])
                ->registerRuntimeField('ELEMENT', [
                    'data_type' => ElementTable::class,
                    'reference' => [
                        '=this.ITEM_ID' => 'ref.ID',
                    ],
                    'join_type' => 'inner',
                ])
                ->exec();

            while ($setItem = $setIterator->fetch()) {
                if ((int)$setItem['IBLOCK_ID'] === $this->iblockFinder->bouquet()) {
                    $result[$setItem['OWNER_ID']]['bouquetId'] = (int)$setItem['ITEM_ID'];
                }

                if ((int)$setItem['IBLOCK_ID'] === $this->iblockFinder->berries()) {
                    $result[$setItem['OWNER_ID']]['berryIds'][] = (int)$setItem['ITEM_ID'];
                }

                if ((int)$setItem['IBLOCK_ID'] === $this->iblockFinder->packing()) {
                    $result[$setItem['OWNER_ID']]['packingId'] = (int)$setItem['ITEM_ID'];
                }
            }

            self::$setProductIds = $result;
        }
    }
}
