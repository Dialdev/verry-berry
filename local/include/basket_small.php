<?php
/** @noinspection PhpIncludeInspection */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

/** @global \CMain $APPLICATION */
$APPLICATION->IncludeComponent(
    'bitrix:sale.basket.basket.small',
    '.default',
    [
        'PATH_TO_BASKET' => '/basket/',	// Страница корзины
        'PATH_TO_ORDER' => '/personal/order/make/',	// Страница оформления заказа
    ],
    false
);
