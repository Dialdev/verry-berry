<?php

namespace Natix\Module\Api\Service\Sale\Order;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\Shipment;
use Natix\Data\Bitrix\UserContainerInterface;
use Natix\Helpers\OrderHelper;
use Natix\Module\Api\ErrorCodes;
use Natix\Module\Api\Exception\Sale\Order\Delivery\DeliveryServiceException;
use Natix\Module\Api\Exception\Sale\Order\OrderServiceException;
use Natix\Module\Api\Exception\Sale\Order\PaySystem\PaySystemServiceException;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketService;
use Natix\Module\Api\Service\Sale\Order\Delivery\DeliveryService;
use Natix\Module\Api\Service\Sale\Order\PaySystem\PaySystemService;
use Natix\Module\Api\Service\User\UserService;
use Natix\Service\Sale\Delivery\Handlers\CourierDeliveryHandler;

/**
 * Сервис создания заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class OrderService
{
    public const PERSON_TYPE_ID_FIZ = 1;

    /** @var BasketService */
    private $basketService;

    /** @var UserService */
    private $userService;

    /** @var DeliveryService */
    private $deliveryService;

    /** @var PaySystemService */
    private $paySystemService;

    /** @var UserContainerInterface */
    private $userContainer;

    /**
     * @param BasketService          $basketService
     * @param UserService            $userService
     * @param DeliveryService        $deliveryService
     * @param PaySystemService       $paySystemService
     * @param UserContainerInterface $userContainer
     */
    public function __construct(
        BasketService $basketService,
        UserService $userService,
        DeliveryService $deliveryService,
        PaySystemService $paySystemService,
        UserContainerInterface $userContainer
    ) {
        $this->basketService = $basketService;
        $this->userService = $userService;
        $this->deliveryService = $deliveryService;
        $this->paySystemService = $paySystemService;
        $this->userContainer = $userContainer;
    }

    /**
     * Создаёт и возвращает заказ на основе данных запроса
     *
     * @param array $requestParams
     * @param bool  $checkEmptyBasket - Проверить и кинуть исключение, если корзина пустая
     * @return Order
     * @throws OrderServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Helpers\OrderHelperException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function getOrder(array $requestParams, $checkEmptyBasket = false): Order
    {
        OrderHelper::setOrderRequestParams($requestParams);
        
        $this->requestParamsValidate($requestParams);

        // создаём заказ и отгрузку
        $basket = $this->basketService->getCurUserBasket();

        if ($checkEmptyBasket && empty($basket->getQuantityList()))
            throw new OrderServiceException('Ваша корзина пуста', ErrorCodes::EMPTY_CART);

        $deliveryDate = $requestParams['PROPERTY_DELIVERY_DATE'] ?? null;

        if ($deliveryDate and CourierDeliveryHandler::isDateInSpecialDay($deliveryDate)) {
            /* @var $basketItem \Bitrix\Sale\BasketItem */
            foreach ($basket->getBasketItems() as $basketItem) {
                $markUp = $basketItem->getBasePrice() / 100 * 30; //наценка 30%

                $basketItem->setField('BASE_PRICE', $basketItem->getBasePrice() + $markUp);
            }
        }

        $order = $this->createOrder($basket, $requestParams);

        $this->setOrderLocation($order, $requestParams['LOCATION']);

        $this->setOrderProperties($order, $requestParams);

        $shipment = $this->createShipment($order);

        if ($requestParams['DELIVERY_ID']) {
            try {
                $this->deliveryService->processDelivery($order, $shipment, $requestParams, true);
            } catch (DeliveryServiceException $e) {
                throw new OrderServiceException($e->getMessage(), $e->getCode(), $e);
            }
        }

        try {
            $this->paySystemService->setPaySystems($order, $requestParams);
        } catch (PaySystemServiceException $e) {
            throw new OrderServiceException($e->getMessage(), $e->getCode(), $e);
        }

        if ($requestParams['IS_PAID']) {
            /** @var Payment $payment */
            foreach ($order->getPaymentCollection() as $payment) {
                if ($payment->isInner()) {
                    continue;
                }

                $payment->setPaid('Y');
            }

            $order->setField('STATUS_ID', OrderHelper::STATUS_PAID);
        }

        $this->setOrderFields($order, $requestParams);

        return $order;
    }

    /**
     * Создаёт и сохраняет заказ
     *
     * @param array $requestParams
     * @return array
     * @throws OrderServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Helpers\OrderHelperException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function orderSave(array $requestParams): array
    {
        $order = $this->getOrder($requestParams, true);

        $order->doFinalAction(true);

        $saveResult = $order->save();

        if (!$saveResult->isSuccess() || $saveResult->getId() <= 0) {
            throw new OrderServiceException(
                implode(
                    ', ',
                    array_merge(
                        $saveResult->getErrorMessages(),
                        $saveResult->getWarningMessages()
                    )
                )
            );
        }

        $this->paySystemService->setBonusPaymentPaid($order, $requestParams);

        $orderId = (int)$saveResult->getId();

        $result = [
            'ORDER_ID'       => $orderId,
            'ORDER_PRICE'    => $order->getPrice(),
            'ORDER_PRODUCTS' => self::getOrderProducts($orderId),
        ];

        return $result;
    }

    /**
     * Получить Id товаров, которые входят в комплект
     *
     * @param int $setId
     * @return array
     */
    public static function getProductsIdsInSet(int $setId): array
    {
        $set = \CCatalogProductSet::getAllSetsByProduct($setId, \CCatalogProductSet::TYPE_SET);

        if (!$set)
            return [];

        $set = current($set);

        if (!isset($set['ITEMS']))
            return [];

        foreach ($set['ITEMS'] as $item)
            $productIds[] = $item['ITEM_ID'];

        return $productIds ?? [];
    }

    /**
     * Получить товары заказа
     *
     * @param int $orderId
     * @return array
     */
    public static function getOrderProducts(int $orderId): array
    {
        $basket = \CSaleBasket::GetList(null, ['ORDER_ID' => $orderId]);

        $excludeIds = [];

        while ($product = $basket->Fetch()) {
            $productId = (int)$product['PRODUCT_ID'];

            if ($product['TYPE'] == \CCatalogProductSet::TYPE_SET)
                $excludeIds = array_merge($excludeIds, self::getProductsIdsInSet($productId));

            if (in_array($productId, $excludeIds))
                continue;

            $group = \CIBlockElement::GetElementGroups($productId, true)->Fetch();

            $rsCrumbs = \CIBlockSection::GetNavChain($group['IBLOCK_ID'], $group['ID']);

            $navChain = '';

            while ($arCrumb = $rsCrumbs->GetNext())
                $navChain .= trim($arCrumb['NAME']).'/';

            $navChain = trim($navChain, '/');

            $products[] = [
                'PRODUCT_ID'        => $productId,
                'NAME'              => $product['NAME'],
                'PRICE'             => $product['PRICE'],
                'QUANTITY'          => $product['QUANTITY'],
                'IBLOCK_ID'         => $group['IBLOCK_ID'],
                'IBLOCK_SECTION_ID' => $group['ID'],
                'NAV_CHAIN'         => $navChain,
            ];
        }

        return $products ?? [];
    }

    /**
     * @param Basket $basket
     * @param array  $requestParams
     * @return Order
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function createOrder(Basket $basket, array $requestParams = []): Order
    {
        $order = Order::create(
            Context::getCurrent()->getSite(),
            $requestParams['USER_ID'] ?? $this->userContainer->getId()
        );

        $order->setPersonTypeId(
            $this->userService->getPersonTypeId(
                $requestParams['PERSON_TYPE_ID'] ?? self::PERSON_TYPE_ID_FIZ
            )
        );

        $order->setMathActionOnly(true);

        $order->setBasket($basket);

        return $order;
    }

    /**
     * Сохраняет местоположение заказа
     *
     * @param Order  $order
     * @param string $location
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Natix\Helpers\OrderHelperException
     */
    public function setOrderLocation(Order $order, string $location)
    {
        OrderHelper::setOrderPropertySingle($order, 'LOCATION', $location, false);
    }

    /**
     * Создаёт отгрузку, добавляя в неё товары из корзины
     *
     * @param Order $order
     * @return Shipment
     * @throws OrderServiceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public function createShipment(Order $order): Shipment
    {
        $shipmentCollection = $order->getShipmentCollection();

        $shipment = $shipmentCollection->createItem();

        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        $result = $shipment->setField('CURRENCY', $order->getCurrency());
        if (!$result->isSuccess()) {
            throw new OrderServiceException(implode(', ', $result->getErrorMessages()));
        }

        /** @var BasketItem $basketItem */
        foreach ($order->getBasket() as $basketItem) {
            $shipmentItem = $shipmentItemCollection->createItem($basketItem);
            $shipmentItem->setQuantity($basketItem->getQuantity());
        }

        return $shipment;
    }

    /**
     * Создаёт и рассчитывает заказ
     *
     * @param array $requestParams
     * @return array
     * @throws OrderServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Helpers\OrderHelperException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function orderCalculate(array $requestParams): array
    {
        $order = $this->getOrder($requestParams, true);

        $result['TOTAL'] = $this->getTotal($order, $requestParams);

        return $result;
    }

    /**
     * Возвращает различные цены, связанные с заказом
     *
     * @param Order $order
     * @param array $requestParams
     * @return array
     */
    public function getTotal(Order $order, array $requestParams): array
    {
        /** @var Order $orderClone */
        $orderClone = $order->createClone();

        $orderPrice = $orderClone->getPrice();
        $deliveryPrice = $orderClone->getDeliveryPrice();
        $discountPrice = 0;

        if ($orderBasket = $order->getBasket()) {
            /** @var BasketItem $basketItem */
            foreach ($orderBasket as $basketItem) {
                $discountPrice += ($basketItem->getQuantity() * $basketItem->getDiscountPrice());
            }
        }

        return [
            'DELIVERY_PRICE'                        => $requestParams['DELIVERY_ID'] ? $deliveryPrice : null,
            'DELIVERY_PRICE_FORMATED'               => $requestParams['DELIVERY_ID']
                ? \CCurrencyLang::CurrencyFormat($deliveryPrice, CurrencyManager::getBaseCurrency())
                : null,
            'DISCOUNT_PRICE'                        => $discountPrice,
            'DISCOUNT_PRICE_FORMATED'               => \CCurrencyLang::CurrencyFormat($discountPrice, CurrencyManager::getBaseCurrency()),
            'DISCOUNT_PRICE_PERCENT'                => $discountPrice > 0
                ? round($discountPrice * 100 / ($orderPrice + $discountPrice), 1)
                : 0,
            'ORDER_PRICE'                           => $orderPrice,
            'ORDER_PRICE_FORMATED'                  => \CCurrencyLang::CurrencyFormat($orderPrice, CurrencyManager::getBaseCurrency()),
            'AMOUNT_PAYABLE_ORDER'                  => $orderPrice,
            'AMOUNT_PAYABLE_ORDER_FORMATED'         => \CCurrencyLang::CurrencyFormat($orderPrice, CurrencyManager::getBaseCurrency()),
            'ORDER_PRICE_WITHOUT_DISCOUNT'          => ($orderPrice + $discountPrice),
            'ORDER_PRICE_WITHOUT_DISCOUNT_FORMATED' => \CCurrencyLang::CurrencyFormat(
                ($orderPrice + $discountPrice),
                CurrencyManager::getBaseCurrency()
            ),
        ];
    }

    /**
     * Валидация входных параметров
     *
     * @param array $requestParams
     * @throws OrderServiceException
     */
    private function requestParamsValidate(array $requestParams): void
    {
        // проверка полей на заполненность
        foreach (
            [
                'LOCATION',
                'PROPERTY_NAME',
                'PROPERTY_EMAIL',
                'PROPERTY_PERSONAL_PHONE',
                'DELIVERY_ID',
                'PAY_SYSTEM_ID',
            ] as $fieldName
        ) {
            if (empty($requestParams[$fieldName])) {
                throw new OrderServiceException(sprintf('Не заполнено поле %s', $fieldName));
            }
        }

        // Возможно ввели сертификат, который полностью оплатил заказ
        $requiredFields = ['DELIVERY_ID', 'PAY_SYSTEM_ID'];

        // проверка полей на заполненность только цифрами
        foreach ($requiredFields as $fieldName) {
            if (
                !preg_match('/^\d+$/', $requestParams[$fieldName])
                || (int)$requestParams[$fieldName] < 1
            ) {
                throw new OrderServiceException(sprintf('Некорректно заполнено поле %s', $fieldName));
            }
        }
    }

    /**
     * Добавляет свойства в заказ
     *
     * @param Order $order
     * @param array $requestParams
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Natix\Helpers\OrderHelperException
     */
    private function setOrderProperties(Order $order, array $requestParams): void
    {
        OrderHelper::setOrderPropertySingle($order, 'NAME', $requestParams['PROPERTY_NAME'], false);
        OrderHelper::setOrderPropertySingle($order, 'EMAIL', $requestParams['PROPERTY_EMAIL'], false);
        OrderHelper::setOrderPropertySingle($order, 'PERSONAL_PHONE', $requestParams['PROPERTY_PERSONAL_PHONE'], false);

        $recipientName = $requestParams['PROPERTY_RECIPIENT_NAME'];
        if (isset($recipientName) && !empty($recipientName)) {
            OrderHelper::setOrderPropertySingle($order, 'RECIPIENT_NAME', $recipientName, false);
        }

        $recipientPhone = $requestParams['PROPERTY_RECIPIENT_PHONE'];
        if (isset($recipientPhone) && !empty($recipientPhone)) {
            OrderHelper::setOrderPropertySingle($order, 'RECIPIENT_PHONE', $recipientPhone, false);
        }

        $needPostcard = $requestParams['PROPERTY_NEED_POSTCARD'];
        if (isset($needPostcard) && !empty($needPostcard)) {
            OrderHelper::setOrderPropertySingle($order, 'NEED_POSTCARD', $needPostcard, false);
        }

        $postcard = $requestParams['PROPERTY_POSTCARD'];
        if (isset($postcard) && !empty($postcard)) {
            OrderHelper::setOrderPropertySingle($order, 'POSTCARD', $postcard, false);
        }

        $recipientMakePhoto = $requestParams['PROPERTY_RECIPIENT_MAKE_PHOTO'];
        if (isset($recipientMakePhoto) && !empty($recipientMakePhoto)) {
            OrderHelper::setOrderPropertySingle($order, 'RECIPIENT_MAKE_PHOTO', $recipientMakePhoto, false);
        }

        $isSurprise = $requestParams['PROPERTY_IS_SURPRISE'];
        if (isset($isSurprise) && !empty($isSurprise)) {
            OrderHelper::setOrderPropertySingle($order, 'IS_SURPRISE', $isSurprise, false);
        }

        $anonymousOrder = $requestParams['PROPERTY_ANONYMOUS_ORDER'];
        if (isset($isSurprise) && !empty($isSurprise)) {
            OrderHelper::setOrderPropertySingle($order, 'ANONYMOUS_ORDER', $anonymousOrder, false);
        }

        $notCallConfirm = $requestParams['PROPERTY_NOT_CALL_CONFIRM'];
        if (isset($notCallConfirm) && !empty($notCallConfirm)) {
            OrderHelper::setOrderPropertySingle($order, 'NOT_CALL_CONFIRM', $notCallConfirm, false);
        }

        $sendPhoto = $requestParams['PROPERTY_SEND_PHOTO'];
        if (isset($sendPhoto) && !empty($sendPhoto)) {
            OrderHelper::setOrderPropertySingle($order, 'SEND_PHOTO', $sendPhoto, false);
        }

        $companyName = $requestParams['PROPERTY_COMPANY_NAME'];
        if (isset($companyName) && !empty($companyName)) {
            OrderHelper::setOrderPropertySingle($order, 'COMPANY_NAME', $companyName, false);
        }

        $fileBill = $requestParams['FILE_BILL'];
        if (isset($sendPhoto) && !empty($sendPhoto)) {
            OrderHelper::setOrderPropertySingle($order, 'FILE_BILL', $fileBill, false);
        }
    }

    /**
     * Заполняет поля заказа
     *
     * @param Order $order
     * @param array $requestParams
     * @throws OrderServiceException
     * @throws \Bitrix\Main\ArgumentException
     */
    private function setOrderFields(Order $order, array $requestParams): void
    {
        if (!empty($requestParams['USER_DESCRIPTION'])) {
            $setResult = $order->setField('USER_DESCRIPTION', $requestParams['USER_DESCRIPTION']);
            if (!$setResult->isSuccess()) {
                throw new OrderServiceException(implode(', ', $setResult->getErrorMessages()));
            }
        }
    }
}
