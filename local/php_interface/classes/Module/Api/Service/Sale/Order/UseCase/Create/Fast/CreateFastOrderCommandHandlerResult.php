<?php

namespace Natix\Module\Api\Service\Sale\Order\UseCase\Create\Fast;

use Webmozart\Assert\Assert;
use Bitrix\Sale\Order;

/**
 * Результат обработчика создания быстрого заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CreateFastOrderCommandHandlerResult
{
    private int $orderId;

    protected Order $order;

    public function __construct(int $orderId)
    {
        Assert::integer($orderId);
        Assert::greaterThan($orderId, 0);

        $this->orderId = $orderId;

        $this->order = Order::load($orderId);
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }


    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }
}
