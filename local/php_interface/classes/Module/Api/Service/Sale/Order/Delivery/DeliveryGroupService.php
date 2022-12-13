<?php

namespace Natix\Module\Api\Service\Sale\Order\Delivery;

use Bitrix\Catalog\StoreTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\Delivery\ExtraServices;
use Bitrix\Sale\Delivery\Restrictions;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Order;
use Natix\Helpers\OrderHelper;
use Natix\Module\Api\Exception\Sale\Order\Delivery\DeliveryGroupServiceException;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketService;
use Natix\Module\Api\Service\Sale\Order\OrderService;

/**
 * Сервис получения служб доставок, сгруппированных по типу доставки
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class DeliveryGroupService
{
    /** @var BasketService */
    private $basketService;
    
    /** @var DeliveryService */
    private $deliveryService;
    
    /** @var OrderService */
    private $orderService;
    
    public function __construct(
        BasketService $basketService,
        DeliveryService $deliveryService,
        OrderService $orderService
    ) {
        $this->basketService = $basketService;
        $this->deliveryService = $deliveryService;
        $this->orderService = $orderService;
    }

    /**
     * Возвращает службы доставки, сгруппированные по типу доставки (в ПВЗ или курьером)
     * @param array $requestParams
     * @return array
     * @throws DeliveryGroupServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\InvalidOperationException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Bitrix\Main\SystemException
     * @throws \Natix\Module\Api\Exception\Sale\Order\Basket\SaleDiscountCalculateException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function getDeliveryByGroup(array $requestParams): array
    {
        $this->requestParamsValidate($requestParams);
        
        $basket = $this->basketService->getCurUserBasket();
        
        $order = $this->orderService->createOrder(
            $basket,
            [
                'PERSON_TYPE_ID' => $requestParams['PERSON_TYPE_ID'],
                'USER_ID' => $requestParams['USER_ID'],
            ]
        );
        
        try {
            $this->orderService->setOrderLocation($order, $requestParams['LOCATION']);
            $this->deliveryService->setDefaultDeliveryInterval($order);
            $shipment = $this->orderService->createShipment($order);
        } catch (\Exception $exception) {
            throw new DeliveryGroupServiceException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        $restrictedObjectsList = Manager::getRestrictedObjectsList(
            $shipment,
            Restrictions\Manager::MODE_CLIENT
        );
        
        $courierDeliveryList = [];
        $pvzDeliveryList = [];
        $checkedDelivery = null;
        $deliveryId = $requestParams['DELIVERY_ID'];
        $priceZaMkad = 0;
        $priceExact = 0;

        foreach ($restrictedObjectsList as $deliveryObj) {
            if ($this->deliveryService->isCourierDelivery($deliveryObj->getCode())) {
                $courierDeliveryList[] = [
                    'ID' => (int)$deliveryObj->getId(),
                    'NAME' => $deliveryObj->getName(),
                    'CODE' => $deliveryObj->getCode(),
                    'PRICE' => (float)$deliveryObj->calculate($shipment)->getDeliveryPrice(),
                ];

                $config = $deliveryObj->getConfig();
                $priceZaMkad = $config['MAIN']['ITEMS']['PRICE_ZA_MKAD']['VALUE'] ?? null;
                $priceExact = $config['MAIN']['ITEMS']['PRICE_EXACT']['VALUE'] ?? null;
            }
            
            if ($this->deliveryService->isPvzDelivery($deliveryObj->getCode())) {
                $storeIds = ExtraServices\Manager::getStoresList($deliveryObj->getId());

                if (!empty($storeIds)) {
                    $pvzDeliveryList = $this->getStores($storeIds);

                    foreach ($pvzDeliveryList as $key => $item) {
                        $pvzDeliveryList[$key]['ID'] = (int)$deliveryObj->getId();
                        $pvzDeliveryList[$key]['STORE_ID'] = (int)$item['ID'];
                        $pvzDeliveryList[$key]['PRICE'] = $deliveryObj->getConfig()['MAIN']['ITEMS']['PRICE']['VALUE']
                            ? (float)$deliveryObj->getConfig()['MAIN']['ITEMS']['PRICE']['VALUE']
                            : 0;
                    }
                }
            }

            if (isset($deliveryId) && (int)$deliveryId === (int)$deliveryObj->getId()) {
                $checkedDelivery = $deliveryObj;
            }
        }

        $result = [];
        
        if (!empty($courierDeliveryList)) {
            $minPrice = $this->getMinPrice($courierDeliveryList);

            $result['courier'] = [
                'TITLE' => 'Курьерская доставка',
                'CHECKED' => ($checkedDelivery && $this->deliveryService->isCourierDelivery($checkedDelivery->getCode()))
                    ? 'Y' : 'N',
                'DELIVERY_MIN_PRICE' => $minPrice,
                'DELIVERY_MIN_PRICE_FORMAT' => $minPrice > 0
                    ? \CCurrencyLang::CurrencyFormat($minPrice, CurrencyManager::getBaseCurrency())
                    : 'Бесплатно',
                'PRICE_ZA_MKAD' => $priceZaMkad,
                'PRICE_ZA_MKAD_FORMAT' => $priceZaMkad > 0
                    ? \CCurrencyLang::CurrencyFormat($priceZaMkad, CurrencyManager::getBaseCurrency())
                    : '',
                'PRICE_EXACT' => $priceExact,
                'PRICE_EXACT_FORMAT' => $priceExact > 0
                    ? \CCurrencyLang::CurrencyFormat($priceExact, CurrencyManager::getBaseCurrency())
                    : '',
                'ROWS' => $courierDeliveryList,
            ];
        }
        
        if (!empty($pvzDeliveryList)) {
            $minPrice = $this->getMinPrice($pvzDeliveryList);

            $result['pvz'] = [
                'TITLE' => 'Самовывоз',
                'CHECKED' => ($checkedDelivery && $this->deliveryService->isPvzDelivery($checkedDelivery->getCode()))
                    ? 'Y' : 'N',
                'DELIVERY_MIN_PRICE' => $minPrice,
                'DELIVERY_MIN_PRICE_FORMAT' => $minPrice > 0
                    ? \CCurrencyLang::CurrencyFormat($minPrice, CurrencyManager::getBaseCurrency())
                    : 'Бесплатно',
                'ROWS' => $pvzDeliveryList,
            ];

        }
        
        return $result;
    }

    /**
     * Валидирует входные данные
     * @param array $requestParams
     * @throws DeliveryGroupServiceException
     */
    private function requestParamsValidate(array $requestParams)
    {
        if (!isset($requestParams['LOCATION']) || empty($requestParams['LOCATION'])) {
            throw new DeliveryGroupServiceException('Не заполнено поле LOCATION');
        }
    }

    /**
     * Возвращает минимальную цену среди доставок
     * @param array $deliveryRows
     * @return float
     */
    private function getMinPrice(array $deliveryRows)
    {
        if (count($deliveryRows) > 1) {
            return min(...array_column($deliveryRows, 'PRICE'));
        }
        foreach ($deliveryRows as $row) {
            return $row['PRICE'];
        }
        return 0.00;
    }

    /**
     * Возвращает данные складов (магазинов)
     * @param array $storeIds
     * @return array
     */
    private function getStores(array $storeIds): array
    {
        if (empty($storeIds)) {
            throw new \InvalidArgumentException('$storeIds должен быть непустым массивом');
        }

        $arr= StoreTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '@ID' => $storeIds,
                '=ACTIVE' => 'Y',
            ])
            ->exec()
            ->fetchAll();
        
        return $arr;
    }
}
