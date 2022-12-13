<?php

namespace Natix\Module\Api\Service\Sale\Order\Basket;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\Discount;
use Bitrix\Sale\Discount\Context\Fuser as ContextFuser;
use Natix\Module\Api\Exception\Sale\Order\Basket\SaleDiscountCalculateException;

/**
 * Применяет скидки правил работы с корзиной к корзине
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SaleDiscountService
{
    /**
     * Пересчитывает цены товаров в корзине с учётом правил работы с корзиной
     *
     * @param Basket $basket
     * @throws SaleDiscountCalculateException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\InvalidOperationException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    public function applySaleDiscountsToBasket(Basket $basket): void
    {
        if ($basket->count() === 0) {
            return;
        }

        $compMode = false;

        if (DiscountCompatibility::isUsed()) {
            DiscountCompatibility::stopUsageCompatible();
            $compMode = true;
        }

        $basket->refreshData(['PRICE', 'COUPONS'], null);

        if ($basket->getOrder())
            $discounts = Discount::buildFromOrder($basket->getOrder());
        else
            $discounts = Discount::buildFromBasket($basket, new ContextFuser($basket->getFUserId(true)));

        $discountCalculateResult = $discounts->calculate();

        if (!$discountCalculateResult->isSuccess()) {
            throw new SaleDiscountCalculateException(
                sprintf(
                    'При расчете скидок произошли ошибки: %s',
                    implode(', ', $discountCalculateResult->getErrorMessages())
                )
            );
        }

        $result = $discounts->getApplyResult(true);

        foreach ($result['PRICES']['BASKET'] as $key => $prices) {
            if ($prices['DISCOUNT'] < 0)
                unset($result['PRICES']['BASKET'][$key]['DISCOUNT']);
        }

        $this->updateBasketPrices($result['PRICES']['BASKET'], $basket);

        if ($compMode)
            DiscountCompatibility::revertUsageCompatible();
    }

    /**
     * Изменяет цены товаров в корзине на цены со скидками
     *
     * @param array  $calculatedPrices массив с рассчитанными ценами с учётом скидок
     * @param Basket $basket
     */
    private function updateBasketPrices(array $calculatedPrices, Basket $basket): void
    {
        foreach ($calculatedPrices as $basketCode => $basketItemData) {
            if (
                ($basketItem = $basket->getItemByBasketCode($basketCode))
                && !$basketItem->isCustomPrice()
                && isset($basketItemData['PRICE'])
            ) {
                $basketItemData['PRICE'] = (float)$basketItemData['PRICE'];

                if ($basketItemData['PRICE'] >= 0 && (float)$basketItem->getPrice() !== $basketItemData['PRICE']) {
                    $basketItem->setField('PRICE', $basketItemData['PRICE']);

                    if (isset($basketItemData['DISCOUNT']))
                        $basketItem->setField('DISCOUNT_PRICE', $basketItemData['DISCOUNT']);
                }
            }
        }
    }
}
