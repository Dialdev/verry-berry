<?php

namespace Natix\Data\Bitrix\Finder\Iblock;

use Maximaster\Tools\Finder\Iblock as BaseIblockFinder;
use Natix\Data\Bitrix\Finder\AbstractFinder;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;

/**
 * Finder идентификаторов инфоблоков
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class IblockFinder extends AbstractFinder
{
    /**
     * Возвращает id инфоблока "Каталог товаров"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function catalog(): int
    {
        $iblockId = BaseIblockFinder::getId('catalogs', 'catalog');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Торговые предложения"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function offers(): int
    {
        $iblockId = BaseIblockFinder::getId('catalogs', 'offers');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Основа букета"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function bouquet(): int
    {
        $iblockId = BaseIblockFinder::getId('catalogs', 'bouquets');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Ягоды"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function berries(): int
    {
        $iblockId = BaseIblockFinder::getId('catalogs', 'berries');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Упаковка"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function packing(): int
    {
        $iblockId = BaseIblockFinder::getId('catalogs', 'packing');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Размеры"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function sizes(): int
    {
        $iblockId = BaseIblockFinder::getId('references', 'sizes');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Слайдер на главной"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function slider(): int
    {
        $iblockId = BaseIblockFinder::getId('content', 'slider');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Баннеры на главной"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function mainBanners(): int
    {
        $iblockId = BaseIblockFinder::getId('content', 'main_banners');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Почему мы?"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function whyUs(): int
    {
        $iblockId = BaseIblockFinder::getId('content', 'why_us');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Подборки товаров на главной"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function selectionMain(): int
    {
        $iblockId = BaseIblockFinder::getId('content', 'selection_main');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }

    /**
     * Возвращает id инфоблока "Фотографии магазина"
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function shopImages(): int
    {
        $iblockId = BaseIblockFinder::getId('content', 'shop_images');

        $this->checkValue($iblockId, __METHOD__);

        return $iblockId;
    }
}
