<?php

namespace Natix\Service\Catalog\Bouquets\Entity;

use Bitrix\Currency\CurrencyManager;


/**
 * Сущность цены (букета, упаковки и т.д.)
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceEntity
{
    /**
     * Розничная цена
     * @var float
     */
    private $price;

    /**
     * Цена с учётом скидок
     * @var float
     */
    private $priceDiscount;

    /**
     * @var string
     */
    private $priceFormat;

    /**
     * @var string
     */
    private $priceDiscountFormat;

    /**
     * @param float $price
     * @param float $priceDiscount
     */
    public function __construct(float $price, float $priceDiscount)
    {
        $this->price = $price;
        $this->priceDiscount = $priceDiscount;
    }

    /**
     * @param PriceEntity $priceEntity
     * @return array
     */
    public static function toState(self $priceEntity): array
    {
        return [
            'price' => $priceEntity->getPrice(),
            'price_discount' => $priceEntity->getPriceDiscount(),
            'price_format' => $priceEntity->getPriceFormat(),
            'price_discount_format' => $priceEntity->getPriceDiscountFormat(),
            'bonus' => intval(\COption::GetOptionString("natix.settings", "bonus_percent")),
        ];
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getPriceDiscount(): float
    {
        return $this->priceDiscount;
    }

    /**
     * Возвращает отформатированную цену
     * @return string
     */
    public function getPriceFormat(): string
    {
        return \CCurrencyLang::CurrencyFormat(
            $this->price,
            CurrencyManager::getBaseCurrency()
        );
    }

    /**
     * Возвращает отформатированную цену со скидкой
     * @return string
     */
    public function getPriceDiscountFormat(): string
    {
        return \CCurrencyLang::CurrencyFormat(
            $this->priceDiscount,
            CurrencyManager::getBaseCurrency()
        );
    }
}
