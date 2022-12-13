<?php

namespace Natix\EventHandlers\Bitrix\Sale;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Order;
use Maximaster\Tools\Events\BaseEvent;
use Natix\Helpers\OrderHelper;

/**
 * Обработчик событий, вызываемых при сохранении, если статус заказа был изменен.
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class OnSaleStatusOrderChange extends BaseEvent
{
    /**
     * Начисляет бонусы партнеру при переводе заказа в статус "Выполнен"
     *
     * @param Order $order
     */
    public static function setPartnerBonuses(Order $order)
    {
        $status = $order->getField('STATUS_ID');
        
        if ($status !== OrderHelper::STATUS_DONE) {
            return;
        }
        
        $partnerId = OrderHelper::getOrderPropertyValueByCode($order, 'PARTNER_ID', true);
        
        if (!$partnerId) {
            return;
        }

        $bonus = (int)Option::get('natix.settings', 'partner_comission', 300);
        $userAccount = \CSaleUserAccount::GetByUserID($partnerId, 'RUB');
        
        if ($userAccount) {
            \CSaleUserAccount::Update($userAccount['ID'], [
                'CURRENT_BUDGET' => $bonus + (float)$userAccount['CURRENT_BUDGET']
            ]);
        } else {
            \CSaleUserAccount::Add([
                'USER_ID' => $partnerId,
                'CURRENCY' => 'RUB',
                'CURRENT_BUDGET' => $bonus,
            ]);
        }

        \CSaleUserTransact::Add([
            'USER_ID' => $partnerId,
            'AMOUNT' => $bonus,
            'CURRENCY' => 'RUB',
            'DEBIT' => 'Y',
            'ORDER_ID' => $order->getId(),
            'EMPLOYEE_ID' => $order->getUserId(),
            'DESCRIPTION' => sprintf('Комиссия партнера за заказ №%s', $order->getId()),
            'TRANSACT_DATE' => new DateTime(),
        ]);
    }
}
