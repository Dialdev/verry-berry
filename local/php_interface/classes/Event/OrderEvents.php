<?php

namespace Natix\Event;

use \Bitrix\Sale;
use Bitrix\Main\Event;
use Bitrix\Sale\Order;

class OrderEvents
{
    private static $propertyUser = 27;

    public static function OnSaleBeforeOrderDelete(Event $event): void
    {
        /* @var $order Order */
        $order = $event->getParameter('ENTITY');

        self::delAllBonuses($order->getId());
    }

    /**
     * Заглушка на что-то, что периодически меняет статус заказа на "Оплачен"
     *
     * @param int    $orderId
     * @param string $statusId
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public static function OnSaleBeforeStatusOrder(int $orderId, string $statusId): bool
    {
        if (\CUser::IsAuthorized() or !$orderId or !$statusId)
            return true;

        $order = Sale\Order::load($orderId);

        if (!$order)
            return true;

        if ($statusId == 'P' and $order->getField('STATUS_ID') != 'N')
            return false;

        return true;
    }

    public static function OnSaleStatusOrderHandler($id, $val)
    {
        $bonusPercent = intval(\COption::GetOptionString("natix.settings", "bonus_percent"));
        $statusID = \COption::GetOptionString("natix.settings", "status_id");

        //Удалить все бонусы, если есть
        self::delAllBonuses($id);

        if (($bonusPercent > 0) && ($statusID == $val)) {
            $order = Sale\Order::load($id);

            $transactions = \CSaleUserTransact::GetList(null, array('ORDER_ID' => $id));

            //если уже есть хоть одно начисление бонусов на этот заказ, - больше не начисляем
            while ($transaction = $transactions->Fetch()) {
                if ($transaction['DEBIT'] == 'Y')
                    return false;
            }

            $paymentCollection = $order->getPaymentCollection();

            //если у заказа есть в оплате "Внутренний счет", - считаем что начислены бонусы
            foreach ($paymentCollection as $collection) {
                if ($collection->getPaymentSystemId() == 1 or $collection->isInner())
                    return false;
            }

            $priceWithoutDelivery = $order->getPrice() - $order->getDeliveryPrice();

            //если уже есть какая-либо скидка, - бонусы не начисляем
            if ($order->getDiscountPrice() or $order->getBasket()->getBasePrice() != $priceWithoutDelivery)
                return false;

            $bonus = intval($priceWithoutDelivery * $bonusPercent / 100);

            self::addBonus($order->getUserId(), $bonus, $id);
        }

        return true;
    }

    /**
     * Удалить все бонусы у заказа
     *
     * @param int $orderId
     * @return void
     */
    protected static function delAllBonuses(int $orderId): void
    {
        $transactions = \CSaleUserTransact::GetList(null, array('ORDER_ID' => $orderId));

        while ($transaction = $transactions->Fetch()) {
            if ($transaction['DEBIT'] == 'Y') {
                \CSaleUserAccount::Withdraw(
                    $transaction["USER_ID"],
                    $transaction["AMOUNT"],
                    $transaction["CURRENCY"],
                    $transaction["ORDER_ID"]
                );
            }

            \CSaleUserTransact::Delete($transaction['ID']);
        }

        $transactions = \CSaleUserTransact::GetList(null, array('ORDER_ID' => $orderId));

        while ($transaction = $transactions->Fetch()) {
            \CSaleUserTransact::Delete($transaction['ID']);
        }
    }

    private function addBonus($user, $bonus, $id)
    {
        $transactParams = [
            'USER_ID'       => $user,
            'AMOUNT'        => $bonus,
            'CURRENCY'      => 'RUB',
            'DEBIT'         => 'Y',
            'ORDER_ID'      => $id,
            'DESCRIPTION'   => sprintf('Зачисление за заказ №%s', $id),
            'TRANSACT_DATE' => date($GLOBALS['DB']->DateFormatToPHP(\CSite::GetDateFormat("FULL", SITE_ID))),
        ];
        $ar = \CSaleUserAccount::GetByUserID($user, 'RUB');
        if ($ar) {
            $bonus = $bonus + floatval($ar['CURRENT_BUDGET']);
            \CSaleUserAccount::Update($ar['ID'], ['CURRENT_BUDGET' => $bonus]);
        }
        else {
            \CSaleUserAccount::Add([
                'USER_ID'        => $user,
                'CURRENCY'       => 'RUB',
                'CURRENT_BUDGET' => $bonus,
            ]);
        }
        \CSaleUserTransact::Add($transactParams);
    }
}
