<?php

namespace Natix\EventHandlers\Bitrix\Main;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\OrderCouponsTable;
use Bitrix\Sale\Order;
use Maximaster\Tools\Events\BaseEvent;
use Natix\Helpers\EnvironmentHelper;

/**
 * Обработчик событий отправки почтовых сообщений
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class OnBeforeEventAdd extends BaseEvent
{
    /** @var null */
    private static $includeSaleModule = null;

    /**
     * Добавляет недостающие поля в шаблон письма о новом и выполненном заказе 
     * 
     * @param $event
     * @param $lid
     * @param $fields
     * @param $messageId
     *
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function saleNewOrder($event, $lid, &$fields, $messageId)
    {
        if (self::$includeSaleModule === null) {
            self::$includeSaleModule = Loader::includeModule('sale');
        }

        $orderId = (int)$fields['ORDER_ID'];

        if (
            in_array($event, ['SALE_NEW_ORDER', 'SALE_STATUS_CHANGED_F', 'SALE_ORDER_CANCEL'])
            && self::$includeSaleModule
            && $orderId > 0
        ) {
            $order = Order::load($orderId);
            
            $fields['ORDER_PRICE_WITHOUT_DELIVERY'] = \CCurrencyLang::CurrencyFormat(
                $order->getPrice() - $order->getDeliveryPrice(),
                CurrencyManager::getBaseCurrency()
            );
            
            $fields['DELIVERY_PRICE_FORMATED'] = \CCurrencyLang::CurrencyFormat(
                $order->getDeliveryPrice(),
                CurrencyManager::getBaseCurrency()
            );
            
            if ($event === 'SALE_ORDER_CANCEL') {
                $fields['PRICE'] = \CCurrencyLang::CurrencyFormat(
                    $order->getPrice(),
                    CurrencyManager::getBaseCurrency()
                );
            }
            
            $coupon = OrderCouponsTable::query()
                ->setSelect(['COUPON'])
                ->setFilter([
                    '=ORDER_ID' => $orderId,
                ])
                ->setLimit(1)
                ->exec()
                ->fetch();
            
            $fields['COUPON'] = $coupon['COUPON'] ?: 'Не применялся';
            
            $fields['SITE_SCHEME'] = EnvironmentHelper::getParam('siteScheme');
            $fields['SITE_HOST'] = EnvironmentHelper::getParam('siteHost');
        }
    }

    /**
     * Если пользователь добавляется при создании заказа, то в письмо передаём пароль
     *
     * @param $event
     * @param $lid
     * @param $fields
     * @param $messageId
     */
    public static function addUserByOrderNew($event, $lid, &$fields, $messageId): void
    {
        if ($event === 'USER_INFO') {
            $rsUser = \CUser::GetList(
                ($by = 'ID'),
                ($order = 'ASC'),
                ['ID' => $fields['USER_ID']],
                ['SELECT' => ['UF_PASSWORD']]
            );
            if ($arUser = $rsUser->Fetch()) {
                $fields['PASSWORD'] = $arUser['UF_PASSWORD'];
            }
        }
    }
}
