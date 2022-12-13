<?php

namespace Natix\Service\Catalog\Bouquets\Service\Factory;

use Bitrix\Catalog\PriceTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlExpression;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Entity\Orm\BouquetTable;
use Natix\Service\Catalog\Bouquets\Entity\BouquetEntity;
use Natix\Service\Catalog\Bouquets\Exception\BouquetFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException;

/**
 * Фабрика для создания объекта сущности букета
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BouquetFactory
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
     * @param IblockFinder $iblockFinder
     * @param PriceTypeFinder $priceTypeFinder
     * @param SizeFactory $sizeFactory
     * @param ImageFactory $imageFactory
     * @param PriceFactory $priceFactory
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
    }

    /**
     * Возвращает сущность букета по его id
     *
     * @param int $id
     * @return BouquetEntity
     * @throws ArgumentException
     * @throws BouquetFactoryException
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PriceFactoryException
     * @throws SizeFactoryException
     */
    public function buildById(int $id): BouquetEntity
    {
        if ($id <= 0) {
            throw new BouquetFactoryException('$id должен быть больше 0');
        }

        $query = BouquetTable::query()
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
                'PREVIEW_PICTURE',
                'PROPERTY_SIZE',
                'PROPERTY_AVAILABLE',
                'PRICE' => 'PRICE_TABLE.PRICE',
            ])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->bouquet(),
                '=ID' => $id,
            ])
            ->registerRuntimeField('PRICE_TABLE', [
                'data_type' => PriceTable::class,
                'reference' => [
                    '=this.ID' => 'ref.PRODUCT_ID',
                    '=ref.CATALOG_GROUP_ID' => new SqlExpression('?i', $this->priceTypeFinder->base())
                ],
                'join_type' => 'left',
            ])
            ->setLimit(1);

        $bouquet = $query->exec()->fetch();

        if (!$bouquet) {
            throw new BouquetFactoryException(sprintf('Букет с id "%s" не найден', $id));
        }

        return new BouquetEntity(
            (int)$bouquet['ID'],
            $bouquet['NAME'],
            $bouquet['CODE'],
            $bouquet['PREVIEW_PICTURE'] > 0
                ? $this->imageFactory->build($bouquet['PREVIEW_PICTURE'], true)
                : null,
            ((int)$bouquet['PROPERTY_AVAILABLE'] === 1),
            $this->priceFactory->build($this->iblockFinder->bouquet(), (int)$bouquet['ID'], (float)$bouquet['PRICE']),
            $bouquet['PROPERTY_SIZE'] ? $this->sizeFactory->buildById($bouquet['PROPERTY_SIZE']) : null
        );
    }
}
