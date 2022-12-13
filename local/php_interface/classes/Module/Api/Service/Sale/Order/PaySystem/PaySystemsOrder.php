<?php

namespace Natix\Module\Api\Service\Sale\Order\PaySystem;

use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem\Manager;
use Natix\Module\Api\Exception\Sale\Order\Delivery\DeliveryServiceException;
use Natix\Module\Api\Exception\Sale\Order\PaySystem\PaySystemServiceException;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketService;
use Natix\Module\Api\Service\Sale\Order\Delivery\DeliveryService;
use Natix\Module\Api\Service\Sale\Order\OrderService;
use Psr\Log\LoggerInterface;

/**
 * Сервис получения платёжных систем для заказа
 * @link https://redmine.book24.ru/issues/30067
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PaySystemsOrder
{
    /** @var BasketService */
    private $basketService;
    
    /** @var DeliveryService */
    private $deliveryService;
    
    /** @var OrderService */
    private $orderService;
    
    /** @var LoggerInterface */
    private $logger;
    
    public function __construct(
        BasketService $basketService,
        DeliveryService $deliveryService,
        OrderService $orderService,
        LoggerInterface $logger
    ) {
        $this->basketService = $basketService;
        $this->deliveryService = $deliveryService;
        $this->orderService = $orderService;
        $this->logger = $logger;
    }

    /**
     * Возвращает список платёжных систем и различные цены, связанные с заказом
     * @param array $requestParams
     * @return array
     * @throws PaySystemServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Helpers\OrderHelperException
     * @throws \Natix\Module\Api\Exception\Sale\Order\OrderServiceException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function getPaySystemsAndTotal(array $requestParams): array
    {
        $result = [
            'PAY_SYSTEM' => [],
            'TOTAL' => [],
        ];

        $this->requestParamsValidate($requestParams);

        $basket = $this->basketService->getCurUserBasket();

        $order = $this->orderService->createOrder($basket, $requestParams);

        $this->orderService->setOrderLocation($order, htmlspecialcharsbx($requestParams['LOCATION']));

        $shipment = $this->orderService->createShipment($order);

        if ($requestParams['DELIVERY_ID']) {
            try {
                $this->deliveryService->setDefaultDeliveryInterval($order);
                $this->deliveryService->processDelivery($order, $shipment, $requestParams, false);
            } catch (DeliveryServiceException $e) {
                throw new PaySystemServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $result['PAY_SYSTEM'] = $this->getPaySystems($order, $requestParams);

        $result['TOTAL'] = $this->orderService->getTotal($order, $requestParams);

        return $result;
    }

    /**
     * Валидирует входные параметры
     * @param array $requestParams
     * @throws PaySystemServiceException
     */
    private function requestParamsValidate(array $requestParams): void
    {
        if (empty($requestParams['LOCATION'])) {
            throw new PaySystemServiceException('Не заполнено поле LOCATION');
        }

        if (empty($requestParams['DELIVERY_ID'])) {
            throw new PaySystemServiceException('Не заполнено поле DELIVERY_ID');
        }

        if (
            !empty($requestParams['DELIVERY_ID'])
            && (
                !preg_match('/^\d+$/', $requestParams['DELIVERY_ID'])
                || (int)$requestParams['DELIVERY_ID'] < 1
            )
        ) {
            throw new PaySystemServiceException('Некорректно заполнено поле DELIVERY_ID');
        }
    }

    /**
     * Получает список платёжных систем
     *
     * @param Order $order
     * @param array $requestParams
     * @return array
     * @throws PaySystemServiceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    private function getPaySystems(Order $order, array $requestParams): array
    {
        $result = [];

        $paymentItem = $order->getPaymentCollection()->createItem();
        $setResult = $paymentItem->setFields([
            'SUM' => $order->getPrice(),
            'CURRENCY' => $order->getCurrency(),
        ]);
        if (!$setResult->isSuccess()) {
            throw new PaySystemServiceException(implode(', ', $setResult->getErrorMessages()));
        }

        $arPaySystemServices = Manager::getListWithRestrictions($paymentItem);
        
        foreach ($arPaySystemServices as $arPaySystem) {
            $result[] = [
                'ID' => (int)$arPaySystem['ID'],
                'CODE' => $arPaySystem['CODE'],
                'NAME' => $arPaySystem['NAME'],
                'DESCRIPTION' => html_entity_decode(trim(strip_tags($arPaySystem['DESCRIPTION']))),
            ];
        }

        return $result;
    }
}
