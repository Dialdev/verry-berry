<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Natix\Module\Api\Http\Action;
use Natix\Module\Api\Middleware\ApiVersionInHeaderMiddleware;
use Natix\Module\Api\Middleware\BitrixCookieAdapterMiddleware;
use Natix\Module\Api\Middleware\CheckBitrixSessidOrTokenMiddleware;
use Natix\Module\Api\Middleware\IncludeBitrixModulesMiddleware;
use Natix\Module\Api\Middleware\ProfilerMiddleware;
use Natix\Module\Api\Middleware\WriteResponseToLogMiddleware;
use Natix\Module\Api\Slim\ApiContainer;

$container = \Natix::$container;

$containerBuilder = new ApiContainer();

$containerBuilder->build($container);

$slimApp = new \Slim\App($container);

$slimContainer = $slimApp->getContainer();

// Pre-processing
$slimApp->add(CheckBitrixSessidOrTokenMiddleware::class);

$slimApp->add(IncludeBitrixModulesMiddleware::class);

$slimApp->add(ProfilerMiddleware::class);

$slimApp->add(BitrixCookieAdapterMiddleware::class);

/** Регистрирует нового пользователя */
$slimApp->post('/api/v1/user/', Action\User\AddAction::class);

/** Обновляет данные пользователя */
$slimApp->put('/api/v1/user/', Action\User\UpdateAction::class);

/** Ответит информацией о текущем авторизованном пользователе */
$slimApp->get('/api/v1/user-session/', Action\User\GetSessionAction::class);

/** Авторизирует пользователя на сайте */
$slimApp->post('/api/v1/user-session/', Action\User\PostSessionAction::class);

/** Получает текущую корзину пользователя */
$slimApp->get('/api/v1/sale/order/basket/', Action\Sale\Order\Basket\GetAction::class);

/** Обновляет количество товара у позиции в корзине */
$slimApp->put('/api/v1/sale/order/basket/item/{basket_item_id}/', Action\Sale\Order\Basket\UpdateAction::class);

/** Удаляет товар из корзины */
$slimApp->delete('/api/v1/sale/order/basket/item/{basket_item_id}/', Action\Sale\Order\Basket\DeleteAction::class);

/** Создаёт и сохраняет новый заказ  */
$slimApp->post('/api/v1/sale/order/save/', Action\Sale\Order\SaveAction::class);

/** Создаёт и сохраняет быстрый заказ */
$slimApp->post('/api/v1/sale/order/saveFast/', Action\Sale\Order\FastOrderAction::class);

/** Получает список доступных доставок для заказа */
$slimApp->get('/api/v1/sale/order/delivery/', Action\Sale\Order\Delivery\GetAction::class);

/** Получает список доступных платёжных систем для заказа */
$slimApp->get('/api/v1/sale/order/paysystem/', Action\Sale\Order\PaySystem\GetAction::class);

/** Создаёт и рассчитывает заказ */
$slimApp->post('/api/v1/sale/order/calculate/', Action\Sale\Order\CalculateAction::class);

/** Запрашивает метод компонента */
$slimApp->get('/api/v1/component/action/{component}/{method}/', Action\Component\ProcessAction::class);

/** Добавляет купон к пользователю к применённым */
$slimApp->post('/api/v1/sale/order/coupon/', Action\Sale\Order\Coupon\AddAction::class);

/** Возвращает список применённых купонов пользователя */
$slimApp->get('/api/v1/sale/order/coupon/', Action\Sale\Order\Coupon\GetAction::class);

/** Удаляет купон из применённых у пользователя */
$slimApp->delete('/api/v1/sale/order/coupon/{coupon}/', Action\Sale\Order\Coupon\DeleteAction::class);

/** Возвращает информацию по доступным бонусам пользователя */
$slimApp->get('/api/v1/sale/order/bonus/', Action\Sale\Order\Bonus\GetAction::class);

/** Получает список товаров для вывода на странице оформления заказа */
$slimApp->get('/api/v1/catalog/products/from_order/', Action\Catalog\GetProductsFromOrderAction::class);

/** Запрашивает доступную комбинацию букета */
$slimApp->get('/api/v1/catalog/product/set/', Action\Catalog\SetProductAction::class);

/** Загружает один файл на сервер */
$slimApp->post('/api/v1/file/upload/', Action\File\UploadAction::class);

// Post-processing
$slimApp->add(ApiVersionInHeaderMiddleware::class);

$slimApp->add(WriteResponseToLogMiddleware::class);

/** @noinspection PhpUnhandledExceptionInspection */
$slimApp->run();
