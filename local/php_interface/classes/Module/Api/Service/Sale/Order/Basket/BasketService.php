<?php

namespace Natix\Module\Api\Service\Sale\Order\Basket;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Internals\BasketTable;
use Natix\Helpers\StringHelper;
use Natix\Module\Api\Exception\Sale\Order\Basket\BasketServiceException;

class BasketService
{
    /** @var SaleDiscountService */
    private $saleDiscountService;
    
    public function __construct(SaleDiscountService $saleDiscountService)
    {
        $this->saleDiscountService = $saleDiscountService;
    }

    /**
     * Возвращает подготовленный массив с информацией о корзине текущего пользователя
     * @return array
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\InvalidOperationException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Module\Api\Exception\Sale\Order\Basket\SaleDiscountCalculateException
     */
    public function getCurUserPreparedBasket(): array
    {
        $basket = $this->getCurUserBasket();

        return $this->getPreparedBasket($basket);
    }

    /**
     * Возвращает объект текущей корзины пользователя с товарами
     * @return Basket
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\InvalidOperationException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Module\Api\Exception\Sale\Order\Basket\SaleDiscountCalculateException
     */
    public function getCurUserBasket(): Basket
    {
        $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());

        $this->saleDiscountService->applySaleDiscountsToBasket($basket);
        
        return $basket;
    }

    /**
     * Возвращает подготовленный массив с информацией о корзине $basket
     *
     * @param Basket $basket
     * @return array
     */
    public function getPreparedBasket(Basket $basket): array
    {
        return [
            'BASKET_ITEMS' => $this->prepareBasketItems($basket),
            'BASKET_TOTALS' => $this->getBasketSummary($basket),
            'FUSER_ID' => $basket->getFUserId(),
        ];
    }

    /**
     * Подготавливает данные товаров корзины для ответа API
     *
     * @param Basket $basket
     * @return array
     */
    private function prepareBasketItems(Basket $basket): array
    {
        $preparedBasketItems = [];

        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $sum = $basketItem->getPrice() * $basketItem->getQuantity();

            $preparedBasketItems[] = [
                'ID' => (int)$basketItem->getId(),
                'PRODUCT_ID' => (int)$basketItem->getProductId(),
                'BASE_PRICE' => $basketItem->getBasePrice(),
                'BASE_PRICE_FORMATTED' => \CCurrencyLang::CurrencyFormat(
                    $basketItem->getBasePrice(),
                    CurrencyManager::getBaseCurrency()
                ),
                'PRICE' => $basketItem->getPrice(),
                'PRICE_FORMATTED' => \CCurrencyLang::CurrencyFormat(
                    $basketItem->getPrice(),
                    CurrencyManager::getBaseCurrency()
                ),
                'DISCOUNT_PERCENT' => $basketItem->getBasePrice() > 0
                    ? round(100 * ($basketItem->getBasePrice() - $basketItem->getPrice()) / $basketItem->getBasePrice())
                    : 0,
                'SUM' => $sum,
                'SUM_FORMATTED' => \CCurrencyLang::CurrencyFormat($sum, CurrencyManager::getBaseCurrency()),
                'DISCOUNT_PRICE' => $basketItem->getDiscountPrice(),
                'DETAIL_PAGE_URL' => $basketItem->getField('DETAIL_PAGE_URL'),
                'QUANTITY' => (int)$basketItem->getQuantity(),
                'WEIGHT' => (float)$basketItem->getWeight(),
            ];
        }

        return $preparedBasketItems;
    }

    /**
     * Получает итоговые значения по корзине (суммарный вес, цену и т.д.)
     * @param Basket $basket
     * @return array
     */
    private function getBasketSummary(Basket $basket)
    {
        $totalWeightGram = $basket->getWeight();
        $totalWeightFormatted = sprintf(
            '%s кг',
            number_format($basket->getWeight() / 1000, 3, '.', ' ')
        );

        $productsCountFormatted = sprintf(
            '%d %s',
            $basket->count(),
            StringHelper::pluralForm($basket->count(), ['товар', 'товара', 'товаров'])
        );

        /** @var float $totalPriceWithoutDiscount  Цена без скидки*/
        $totalPriceWithoutDiscount = $basket->getBasePrice();
        $totalPriceWithoutDiscountFormatted = \CCurrencyLang::CurrencyFormat(
            $totalPriceWithoutDiscount,
            CurrencyManager::getBaseCurrency()
        );

        /** @var float $totalPrice Цена со скидкой*/
        $totalPrice = $basket->getPrice();
        $totalPriceFormatted = \CCurrencyLang::CurrencyFormat($totalPrice, CurrencyManager::getBaseCurrency());

        $discountAbsoluteValue = $totalPriceWithoutDiscount - $totalPrice;
        $discountAbsoluteValueFormatted = \CCurrencyLang::CurrencyFormat(
            $discountAbsoluteValue,
            CurrencyManager::getBaseCurrency()
        );

        $discountPercentValue = ($totalPriceWithoutDiscount > 0)
            ? round(($discountAbsoluteValue / $totalPriceWithoutDiscount) * 100, 1)
            : 0;

        return [
            'PRODUCTS_COUNT' => $basket->count(),
            'PRODUCTS_COUNT_FORMATTED' => $productsCountFormatted,
            'PRODUCTS_WEIGHT' => $totalWeightGram,
            'PRODUCTS_WEIGHT_FORMATTED' => $totalWeightFormatted,
            'TOTAL_PRODUCTS_PRICE' => $totalPrice,
            'TOTAL_PRODUCTS_PRICE_FORMATTED' => $totalPriceFormatted,
            'TOTAL_PRODUCTS_PRICE_WITHOUT_DISCOUNT' => $totalPriceWithoutDiscount,
            'TOTAL_PRODUCTS_PRICE_WITHOUT_DISCOUNT_FORMATTED' => $totalPriceWithoutDiscountFormatted,
            'DISCOUNT_ABSOLUTE_VALUE' => $discountAbsoluteValue,
            'DISCOUNT_ABSOLUTE_VALUE_FORMATTED' => $discountAbsoluteValueFormatted,
            'DISCOUNT_PERCENT_VALUE' => $discountPercentValue,
        ];
    }

    /**
     * Обновляет кол-во товара в текущей корзине пользователя
     *
     * @param int $basketItemId
     * @param int $quantity
     * @return bool
     * @throws BasketServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public function updateBasketItemQuantity(int $basketItemId, int $quantity): bool
    {
        $basket = $this->getCurUserBasket();
        $basketItem = $basket->getItemById($basketItemId);

        if (!$basketItem) {
            throw new BasketServiceException(sprintf(
                'по переданному id "%s" не найдена позиция в корзине',
                $basketItemId
            ));
        }

        $basketItem->setFields(['QUANTITY' => $quantity,]);
        $saveResult = $basket->save();

        if (!$saveResult->isSuccess()) {
            throw new BasketServiceException(
                sprintf(
                    'Ошибка изменения количества в корзине: %s',
                    implode(', ', $saveResult->getErrorMessages())
                )
            );
        }

        return true;
    }

    /**
     * Удаляет товар из корзины текущего пользователя
     *
     * @param int $basketItemId ИД записи в корзине
     * @throws BasketServiceException
     * @throws \Exception
     */
    public function deleteFromBasket(int $basketItemId)
    {
        /**
         * Удаляем товары через ORM, а не через объект корзины, т.к. в противном случае на хите, при работе с корзиной
         * не смотря на то что объекта данного товара в корзине не будет, на него будут пристутствовать ссылки у
         * других товаров корзины. Как следствие - не корректно ведут себя акции (рассчитаватся скидки так, будто
         * удаленный товар находится в корзине)
         */
        $result = BasketTable::deleteBundle($basketItemId);

        if (!$result->isSuccess()) {
            throw new BasketServiceException(
                sprintf(
                    'Ошибка удаления записи %d: %s',
                    $basketItemId,
                    implode(', ', $result->getErrorMessages())
                )
            );
        }
    }

    /**
     * Возвращает список идентификаторов товаров текущей корзины пользователя
     *
     * @return array
     * @throws \Exception
     */
    public function getCurUserBasketProductIds(): array
    {
        $productIds = [];
        $basket = $this->getCurUserBasket();
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $productIds[] = (int)$basketItem->getProductId();
        }
        
        return $productIds;
    }
}
