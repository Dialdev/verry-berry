<?php

namespace Natix\Module\Api;

/**
 * Коды ошибок API. То, что помещается в ответе под ключом CODE
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ErrorCodes
{
    /**
     * Когда корзина пустая
     */
    const EMPTY_CART = 1000;

    /**
     * Когда переданный параметр SESSID не прошёл валидацию
     */
    const CHECK_BITRIX_SESSION = 1010;

    /**
     * Список служб доставки для текущего пользователя пуст
     */
    const DELIVERY_LIST_EMPTY = 1020;

    /**
     * Список способов оплаты для текущего пользователя пуст
     */
    const PAY_SYSTEM_LIST_EMPTY = 1030;

    /**
     * Email уже подписан на оповещение о появлении товара
     */
    const PRODUCT_ARRIVAL_ALREADY_SUBSCRIBED = 1040;

    /**
     * Не верный формат даты доставки
     */
    const DELIVERY_DATE_WRONG_FORMAT = 1050;

    /**
     * Срок действия купона (акции по купону) истек
     */
    const COUPON_EXPIRED = 1060;

    /**
     * Заказ не найден
     */
    const ORDER_NOT_FOUND = 1070;

    /**
     * Номер карты много.ру не существует
     */
    const MNOGO_RU_CARD_NOT_FOUND = 1080;

    /**
     * Заказ не может быть отменен
     */
    const ORDER_CAN_NOT_BE_CANCELLED = 1090;

    /**
     * Доступ запрещен
     */
    const ACCESS_DENIED = 1100;
}
