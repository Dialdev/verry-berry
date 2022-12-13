<?php

namespace Natix\Module\Api\Service\Sale\Order\UseCase\Create\Fast;

use Bitrix\Catalog\PriceTable;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Context;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItem;
use Natix\Data\Bitrix\Finder\Sale\PaySystemFinder;
use Natix\Helpers\OrderHelper;
use Natix\Module\Api\Exception\Sale\Order\Delivery\DeliveryServiceException;
use Natix\Module\Api\Exception\Sale\Order\OrderServiceException;
use Natix\Module\Api\Exception\Sale\Order\PaySystem\PaySystemServiceException;
use Natix\Module\Api\Service\User\UserService;

/**
 * Обработчик создания быстрого заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CreateFastOrderCommandHandler
{
    const PERSON_TYPE_ID_FIZ = 1;
    
    //служба доставки для быстрого заказа 
    private $deliveryIdMap = [
        '0c5b2444-70a0-4932-980c-b4dc0d3f02b5' => [
            'ID' => 22,
            'NAME' => 'Самовывоз в Москве',
        ],
        'c2deb16a-0330-4f05-821f-1d09c93331e6' => [
            'ID' => 23,
            'NAME' => 'Самовывоз в Санкт-Петербурге',
        ],        
        'd7a10054-da31-4560-8121-7c2f33092883' => [
            'ID' => 24,
            'NAME' => 'Самовывоз в Краснодаре',
        ],
    ];

    /** @var UserService */
    private $userService;
    
    /** @var PaySystemFinder */
    private $paySystemFinder;
    
    public function __construct(UserService $userService, PaySystemFinder $paySystemFinder)
    {
        $this->userService = $userService;
        $this->paySystemFinder = $paySystemFinder;
    }

    /**
     * @param CreateFastOrderCommand $command
     *
     * @return CreateFastOrderCommandHandlerResult
     * @throws OrderServiceException
     */
    public function handle(CreateFastOrderCommand $command): CreateFastOrderCommandHandlerResult
    {
        $basket = $this->getBasket($command->getProductId());
        if (empty($basket->getQuantityList())) {
            throw new OrderServiceException(
                sprintf('Корзина пуста. Не удалсь добавить товар "%s" в корзину', $command->getProductId())
            );
        }

        $order = $this->createOrder($basket, $command);

        OrderHelper::setOrderPropertySingle($order, 'LOCATION', $command->getLocation(), false);

        $this->setOrderProperties($order, $command);

        $shipment = $this->createShipment($order, $command);
        
        $this->setPaySystem($order);

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
        
        return new CreateFastOrderCommandHandlerResult((int)$saveResult->getId());
    }

    /**
     * Создаёт объект корзины заказа с переданным товаром
     *
     * @param int $productId
     *
     * @return Basket
     * @throws \Bitrix\Main\ArgumentException
     */
    private function getBasket(int $productId): Basket
    {
        $basket = Basket::create(Context::getCurrent()->getSite());

        $product = $this->getProductById($productId);
        
        $basketItem = $basket->createItem('catalog', $productId);

        $fields = [
            'PRODUCT_ID' => $productId,
            'QUANTITY' => 1,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => Context::getCurrent()->getSite(),
            'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
            'CATALOG_XML_ID' => $product['IBLOCK_XML_ID'],
            'PRODUCT_XML_ID' => $product['XML_ID'],
        ];

        $resultSetFields = $basketItem->setFields($fields);

        if (!$resultSetFields->isSuccess()) {
            throw new \RuntimeException(
                sprintf(
                    'Ошибка добавления товара id %d в корзину: %s',
                    $productId,
                    implode(
                        ', ',
                        $resultSetFields->getErrorMessages()
                    )
                )
            );
        }
        
        return $basket;
    }

    /**
     * @param int $productId
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    private function getProductById(int $productId): array
    {
        $iblockElement = ElementTable::getRow([
            'select' => [
                'IBLOCK_ID',
                'XML_ID',
                'IBLOCK_XML_ID' => 'IBLOCK.XML_ID',
            ],
            'filter' => [
                '=ID' => $productId,
                'ACTIVE' => 'Y',
            ],
            'runtime' => [
                new ReferenceField(
                    'PRICE_ELEMENT',
                    PriceTable::class,
                    ['=this.ID' => 'ref.PRODUCT_ID'],
                    ['join_type' => 'inner']
                ),
            ],
        ]);
        
        if (!$iblockElement) {
            throw new \RuntimeException(sprintf('Товар id %d не найден', $productId));
        }

        return $iblockElement;
    }

    /**
     *
     *
     * @param Basket $basket
     * @param CreateFastOrderCommand $command
     *
     * @return Order
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    private function createOrder(Basket $basket, CreateFastOrderCommand $command): Order
    {
        $order = Order::create(
            Context::getCurrent()->getSite(),
            $command->getUserId()
        );

        $order->setPersonTypeId(
            $this->userService->getPersonTypeId(
                $requestParams['PERSON_TYPE_ID'] ?? self::PERSON_TYPE_ID_FIZ
            )
        );

        $order->setBasket($basket);

        return $order;
    }

    /**
     * Добавляет свойства в заказ
     *
     * @param Order $order
     * @param CreateFastOrderCommand $command
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Natix\Helpers\OrderHelperException
     */
    private function setOrderProperties(Order $order, CreateFastOrderCommand $command): void
    {
        OrderHelper::setOrderPropertySingle($order, 'NAME', $command->getPropertyName(), false);
        OrderHelper::setOrderPropertySingle($order, 'EMAIL', $command->getPropertyEmail(), false);
        OrderHelper::setOrderPropertySingle($order, 'PERSONAL_PHONE', $command->getPropertyPersonalPhone(), false);
        OrderHelper::setOrderPropertySingle($order, 'FAST_ORDER', 'Y', false);
    }

    /**
     * Создаёт отгрузку, добавляя в неё товары из корзины
     *
     * @param Order $order
     * @param CreateFastOrderCommand $command
     *
     * @return Shipment
     * @throws OrderServiceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    private function createShipment(Order $order, CreateFastOrderCommand $command): Shipment
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
            if ($shipmentItem instanceof ShipmentItem) {
                $shipmentItem->setQuantity($basketItem->getQuantity());
            }
        }

        $isDeliverySet = false;
        
        $delivery = $this->deliveryIdMap[$command->getLocation()];
        
        if (!$delivery) {
            throw new \RuntimeException('Не найдены данные службы доставки по коду местоположения');
        }

        $setResult = $shipment->setFields([
            'DELIVERY_ID' => $delivery['ID'],
            'DELIVERY_NAME' => $delivery['NAME'],
            'CURRENCY' => $order->getCurrency(),
        ]);
        
        if (!$setResult->isSuccess()) {
            throw new DeliveryServiceException(implode(', ', $setResult->getErrorMessages()));
        }

        $isDeliverySet = true;

        if (!$isDeliverySet) {
            throw new DeliveryServiceException('Служба доставки не найдена или недоступна');
        }

        return $shipment;
    }

    /**
     * Устанавливает основную систему оплаты
     *
     * @param Order $order
     * @param array $requestParams
     * @param int $paySystemId
     * @param float $price
     *
     * @throws PaySystemServiceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    private function setPaySystem(Order $order): void
    {
        $paymentItem = $order->getPaymentCollection()->createItem();
        $setResult = $paymentItem->setFields([
            'SUM' => $order->getPrice(),
            'CURRENCY' => $order->getCurrency(),
        ]);
        if (!$setResult->isSuccess()) {
            throw new PaySystemServiceException(implode(', ', $setResult->getErrorMessages()));
        }

        $arPaySystemServices = Manager::getListWithRestrictions($paymentItem);

        $isPaySystem = false;
        $paySystemId = $this->paySystemFinder->cash();
        foreach ($arPaySystemServices as $arPaySystem) {
            if ($paySystemId === (int)$arPaySystem['ID']) {
                $setResult = $paymentItem->setFields([
                    'PAY_SYSTEM_ID' => $arPaySystem['ID'],
                    'PAY_SYSTEM_NAME' => $arPaySystem['NAME'],
                ]);
                if (!$setResult->isSuccess()) {
                    throw new PaySystemServiceException(implode(', ', $setResult->getErrorMessages()));
                }

                $isPaySystem = true;
                break;
            }
        }

        if (!$isPaySystem) {
            throw new PaySystemServiceException(
                sprintf('Платёжная система PAY_SYSTEM_ID=%s не найдена', $paySystemId)
            );
        }
    }
}
