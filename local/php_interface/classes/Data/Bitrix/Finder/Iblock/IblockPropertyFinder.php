<?php

namespace Natix\Data\Bitrix\Finder\Iblock;

use Natix\Data\Bitrix\Finder\AbstractFinder;
use Maximaster\Tools\Finder\IblockProperty as BaseIblockProperty;

/**
 * Finder идентификаторов свойств элементов инфоблоков
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class IblockPropertyFinder extends AbstractFinder
{
    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @param IblockFinder $iblockFinder
     */
    public function __construct(IblockFinder $iblockFinder)
    {
        $this->iblockFinder = $iblockFinder;
    }

    /**
     * Свойство "Размер" инфоблока Букеты
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function bouquetSizeId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->bouquet(), 'SIZE');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "В наличии" инфоблока Букеты
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function bouquetAvailableId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->bouquet(), 'AVAILABLE');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "Выводить в каталоге" инфоблока Букеты
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function bouquetCatalogListId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->bouquet(), 'CATALOG_LIST');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "Артикул" инфоблока Букеты
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function bouquetArticulId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->bouquet(), 'ARTICUL');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "Для букета размером" инфоблока Ягоды
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function berriesSizeId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->berries(), 'SIZE');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "В наличии" инфоблока Ягоды
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function berriesAvailableId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->berries(), 'AVAILABLE');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "В наличии" инфоблока Упаковка
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function packingAvailableId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->packing(), 'AVAILABLE');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "Дополнительные фото" инфоблока "Каталог товаров"
     *
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function catalogDopImagesId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->catalog(), 'DOP_IMAGES');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "Товары подборки" инфоблока Подборки товаров на главной
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function selectionMaimProductsId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->selectionMain(), 'PRODUCTS');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }

    /**
     * Свойство "Выводить на странице оформления заказа" инфоблока "Каталог товаров"
     *
     * @return int
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function catalogShowInOrderId(): int
    {
        $propertyId = BaseIblockProperty::getId($this->iblockFinder->catalog(), 'SHOW_IN_ORDER');

        $this->checkValue($propertyId, __METHOD__);

        return $propertyId;
    }
}
