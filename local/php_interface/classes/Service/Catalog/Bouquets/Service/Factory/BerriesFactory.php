<?php

namespace Natix\Service\Catalog\Bouquets\Service\Factory;

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Entity\Orm\BerriesTable;
use Natix\Service\Catalog\Bouquets\Collection\BerryEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\BerryEntity;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;
use Natix\Service\Catalog\Bouquets\Exception\BerriesFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException;

/**
 * Фабрика для создания объектов сущностей дополнительных ягод, добавляемых в букет
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BerriesFactory
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
     * @var SizeFactory
     */
    private $sizeFactory;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var PriceFactory
     */
    private $priceFactory;

    /**
     * @var BerryEntityCollection
     */
    private static $berriesMap;

    /**
     * @param IblockFinder $iblockFinder
     * @param PriceTypeFinder $priceTypeFinder
     * @param SizeFactory $sizeFactory
     * @param ImageFactory $imageFactory
     * @param PriceFactory $priceFactory
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PriceFactoryException
     * @throws SizeFactoryException
     * @throws LoaderException
     */
    public function __construct(
        IblockFinder $iblockFinder,
        PriceTypeFinder $priceTypeFinder,
        SizeFactory $sizeFactory,
        ImageFactory $imageFactory,
        PriceFactory $priceFactory
    ) {
        $this->iblockFinder = $iblockFinder;
        $this->priceTypeFinder = $priceTypeFinder;
        $this->sizeFactory = $sizeFactory;
        $this->imageFactory = $imageFactory;
        $this->priceFactory = $priceFactory;

        $this->prepareBerriesMap();
    }

    /**
     * Возвращает коллекцию дополнительных ягод для букета по id ягод
     * @param array $berryIds
     * @return BerryEntityCollection
     * @throws BerriesFactoryException
     */
    public function buildByIds(array $berryIds): BerryEntityCollection
    {
        if (empty($berryIds)) {
            throw new BerriesFactoryException('Массив id ягод не может быть пустым');
        }

        $collection = new BerryEntityCollection();

        /** @var BerryEntity $berryEntity */
        foreach (self::$berriesMap->getIterator() as $berryEntity) {
            if (in_array($berryEntity->getId(), $berryIds, true)) {
                $collection->set($berryEntity->getId(), $berryEntity);
            }
        }

        return $collection;
    }

    /**
     * Создаёт коллекцию со всеми имеющимися дополнительными ягодами для букетов
     * @return BerryEntityCollection
     */
    public function buildAllBerries(): BerryEntityCollection
    {
        return self::$berriesMap;
    }

    /**
     * Создает коллекцию дополнительных ягод по переданному размеру букета
     * @param SizeEntity $sizeEntity
     * @return BerryEntityCollection
     */
    public function buildBySize(SizeEntity $sizeEntity): BerryEntityCollection
    {
        $collection = new BerryEntityCollection();

        /** @var BerryEntity $berryEntity */
        foreach (self::$berriesMap->getIterator() as $berryEntity) {
            if ($berryEntity->isContainsSize($sizeEntity)) {
                $collection->set($berryEntity->getId(), $berryEntity);
            }
        }

        return $collection;
    }

    /**
     * Создает сущность дополнительной ягоды из переданныз данных
     * @param array $berry
     * @return BerryEntity
     * @throws BerriesFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws SizeFactoryException
     * @throws PriceFactoryException
     */
    public function buildByRow(array $berry): BerryEntity
    {
        if (empty($berry)) {
            throw new BerriesFactoryException('$berry должен быть не пустым массивом');
        }

        return new BerryEntity(
            (int)$berry['ID'],
            $berry['NAME'],
            $berry['PROPERTY_CARD_NAME'],
            $berry['PROPERTY_SIZE'] ? $this->sizeFactory->buildById($berry['PROPERTY_SIZE']) : null,
            $berry['PREVIEW_PICTURE'] > 0
                ? $this->imageFactory->build($berry['PREVIEW_PICTURE'], true)
                : null,
            ((int)$berry['PROPERTY_AVAILABLE'] === 1),
            $this->priceFactory->build($this->iblockFinder->berries(), (int)$berry['ID'], (float)$berry['PRICE']),
            (int)$berry['IBLOCK_SECTION_ID']
        );
    }

    /**
     * Возвращает базовый объект запроса
     * @return Query
     * @throws FinderEmptyValueException
     * @throws ArgumentException
     */
    private function getBaseQuery(): Query
    {
        $query = BerriesTable::query()
            ->setSelect([
                'ID',
                'NAME',
                'PREVIEW_PICTURE',
                'IBLOCK_SECTION_ID',
                'PRICE' => 'PRICE_TABLE.PRICE',
                'PROPERTY_SIZE',
                'PROPERTY_AVAILABLE',
                'PROPERTY_CARD_NAME',
            ])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->berries(),
            ])
            ->registerRuntimeField('PRICE_TABLE', [
                'data_type' => PriceTable::class,
                'reference' => [
                    '=this.ID' => 'ref.PRODUCT_ID',
                    '=ref.CATALOG_GROUP_ID' => new SqlExpression('?i', $this->priceTypeFinder->base()),
                ],
                'join_type' => 'left',
            ]);

        return $query;
    }

    /**
     * Подготавливает маппинг с коллекцией всех ягод
     * @throws ArgumentException
     * @throws BerriesFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PriceFactoryException
     * @throws SizeFactoryException
     * @throws LoaderException
     */
    private function prepareBerriesMap(): void
    {
        if (self::$berriesMap === null) {
            Loader::includeModule('iblock');

            $iterator = $this->getBaseQuery()->exec();

            self::$berriesMap = new BerryEntityCollection();

            while ($item = $iterator->fetch()) {
                self::$berriesMap->set($item['ID'], $this->buildByRow($item));
            }
        }
    }
}
