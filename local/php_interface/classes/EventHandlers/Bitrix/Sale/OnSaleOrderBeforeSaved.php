<?php

namespace Natix\EventHandlers\Bitrix\Sale;

use Bitrix\Main\Context;
use Bitrix\Main\Web\Cookie;
use Bitrix\Sale\Order;
use Maximaster\Tools\Events\BaseEvent;
use Natix\EventHandlers\Bitrix\Main\OnProlog;
use Natix\Helpers\OrderHelper;

/**
 * Обработчики событий, вызываемых перед созданием заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class OnSaleOrderBeforeSaved extends BaseEvent
{
    const ORDER_PARTNER_ID_FIELD_NAME = 'PARTNER_ID';

    /**
     * Устанавливает в свойство заказа идентификатор партнера, если он есть в куках пользователя
     * 
     * @param Order $order
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Natix\Helpers\OrderHelperException
     */
    public static function setPartner(Order $order)
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();
        $partnerId = $request->getCookie(OnProlog::PARTNER_ID_COOKIE_NAME);
        
        if ($partnerId > 0) {
            OrderHelper::setOrderPropertySingle($order, self::ORDER_PARTNER_ID_FIELD_NAME, $partnerId);

            $context = Context::getCurrent();
            $cookie = new Cookie(OnProlog::PARTNER_ID_COOKIE_NAME, '', time());
            $cookie->setPath('/');
            $cookie->setDomain($context->getServer()->getHttpHost());
            $context->getResponse()->addCookie($cookie);
        }
    }
}
