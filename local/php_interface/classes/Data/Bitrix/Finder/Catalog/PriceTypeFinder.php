<?php

namespace Natix\Data\Bitrix\Finder\Catalog;

use Maximaster\Tools\Finder\PriceType as BasePriceTypeFinder;
use Natix\Data\Bitrix\Finder\AbstractFinder;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;

/**
 * Finder идентификаторов типов цен
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceTypeFinder extends AbstractFinder
{
    /**
     * Возвращает id базового типа цены
     * @return int
     * @throws FinderEmptyValueException
     */
    public function base(): int
    {
        $priceTypeId = BasePriceTypeFinder::getId('BASE');

        $this->checkValue($priceTypeId, __METHOD__);

        return $priceTypeId;
    }
}
