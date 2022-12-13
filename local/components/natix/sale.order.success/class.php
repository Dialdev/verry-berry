<?php

namespace Natix\Component;

use Bitrix\Sale\Order;
use Natix\Module\Api\Service\Sale\Order\OrderService;

/**
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SaleOrderSuccess extends CommonComponent
{
    /** @var array */
    protected $needModules = [
        'sale',
    ];

    /** @var bool */
    protected $cacheTemplate = false;

    /** @var Order */
    private $order;

    protected function configurate()
    {
        $this->arParams['ORDER_ID'] = (int)$this->arParams['ORDER_ID'];

        $this->addCacheAdditionalId($this->arParams['ORDER_ID']);
    }

    protected function executeMain()
    {
        try {
            if ($this->arParams['ORDER_ID'] <= 0) {
                throw new \Exception('Не передан номер заказа');
            }

            $this->order = Order::load($this->arParams['ORDER_ID']);

            if ($this->order === null) {
                throw new \Exception(sprintf('Заказ №%s не существует', $this->arParams['ORDER_ID']));
            }
            $orderId = $this->order->getId();

            $products = OrderService::getOrderProducts($orderId);

            $productNames = $productChains = '';
            
            foreach ($products as $product) {
                $productIds[] = $product['PRODUCT_ID'];

                $productNames .= $product['NAME']. ' / ';

                if ($product['NAV_CHAIN'])
                    $productChains .= $product['NAV_CHAIN']. ', ';
            }

            $this->arResult['ORDER_ID'] = $orderId;

            $this->arResult['ORDER'] = $this->order;

            $this->arResult['ORDER_PRODUCTS'] = $products;

            $this->arResult['ORDER_PRODUCTS_IDS'] = $productIds ?? [];
            
            $this->arResult['ORDER_PRODUCTS_NAMES'] = $productNames ?? [];
            
            $this->arResult['ORDER_PRODUCTS_CHAINS'] = $productChains ?? [];
        } catch (\Exception $exception) {
            $this->arResult['ERROR_MESSAGE'] = $exception->getMessage();
        }
    }
}
