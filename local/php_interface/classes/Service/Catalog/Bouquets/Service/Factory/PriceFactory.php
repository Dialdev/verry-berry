<?php

namespace Natix\Service\Catalog\Bouquets\Service\Factory;

use Bitrix\Currency\CurrencyManager;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Service\Catalog\Bouquets\Entity\PriceEntity;
use Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException;

/**
 * Фабрика для создания сущности с ценой
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceFactory
{
    /**
     * @var PriceTypeFinder
     */
    private $priceTypeFinder;

    /**
     * @param PriceTypeFinder $priceTypeFinder
     */
    public function __construct(PriceTypeFinder $priceTypeFinder)
    {
        $this->priceTypeFinder = $priceTypeFinder;
    }

    /**
     * Создаёт сущность с ценой
     * @param int $iblockId
     * @param int $productId
     * @param float $price
     * @return PriceEntity
     * @throws FinderEmptyValueException
     * @throws PriceFactoryException
     */
    public function build(int $iblockId, int $productId, float $price): PriceEntity
    {
        if ($iblockId <= 0) {
            throw new PriceFactoryException('$iblockId должен быть больше 0');
        }

        if ($productId <= 0) {
            throw new PriceFactoryException('$productId должен быть больше 0');
        }

        $discounts = \CCatalogDiscount::GetDiscount(
            $productId,
            $iblockId,
            [$this->priceTypeFinder->base()],
            [],
            'N',
            false,
            false
        );

        $priceDiscount = $price;

        if (!empty($discounts)) {
            $priceDiscount = \CCatalogProduct::CountPriceWithDiscount(
                $price,
                CurrencyManager::getBaseCurrency(),
                $discounts
            );
        }

        return new PriceEntity($price, $priceDiscount);
    }
}
