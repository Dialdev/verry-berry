<?php

namespace Natix\Module\Api\Http\Action\Sale\Order;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\OrderService;
use Natix\Module\Api\Slim\Request;
use Natix\Module\Api\Slim\Response;

/**
 * Action создания и расчёта заказа
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CalculateAction
{
    /** @var OrderService */
    private $orderService;
    
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     summary="Создаёт и рассчитывает заказ",
     *     description="",
     *     path="/api/v1/sale/order/calculate/",
     *     operationId="v1-sale-order-calculate",
     *     tags={"order"},
     *     @OA\Parameter(
     *         name="LOCATION", in="query", required=true, example="0c5b2444-70a0-4932-980c-b4dc0d3f02b5",
     *         description="Идентификатор местоположения.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="DELIVERY_ID", in="query", required=true, example=19,
     *         description="Идентификатор службы доставки.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="PAY_SYSTEM_ID", in="query", required=true, example=2,
     *         description="Идентификатор платёжной системы.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="PERSON_TYPE_ID", in="query", required=true, example=1,
     *         description="Идентификатор типа плательщика. Передаем всегда 1 - физическое лицо.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="USER_DESCRIPTION", in="query", required=false, example="Перезвоните перед доставкой",
     *         description="Комментарий покупателя к заказу.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_NAME", in="query", required=true, example="Иван Иванов",
     *         description="Имя пользователя.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_EMAIL", in="query", required=true, example="ivan@gmail.com",
     *         description="E-mail пользователя.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_PERSONAL_PHONE", in="query", required=true, example="+71234567890",
     *         description="Номер телефона пользователя.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_GET_ADDRESS", in="query", required=false, example="N",
     *         description="Узнать адрес у получателя.",
     *         @OA\Schema(type="string", enum={"Y", "N"})
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_CITY", in="query", required=false, example="Москва",
     *         description="Город доставки. Параметр обязателен, если в заказе используется курьерская доставка и не установлен флажок Узнать адрес у получателя.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_STREET", in="query", required=false, example="Новослободская",
     *         description="Улица. Параметр обязателен, если в заказе используется курьерская доставка и не установлен флажок Узнать адрес у получателя.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_HOME", in="query", required=false, example="45с1",
     *         description="Дом/корпус. Параметр обязателен, если в заказе используется курьерская доставка и не установлен флажок Узнать адрес у получателя.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_APARTMENT", in="query", required=false, example="303",
     *         description="Квартира/офис.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_DISTANCE", in="query", required=false, example=25,
     *         description="Расстояние от МКАД|КАД. Параметр обязателен, если в заказе используется курьерская доставка и адрес доставки за МКАД|КАД и не установлен флажок Узнать адрес у получателя. На основе этого параметра будет рассчитана итоговая стоимость доставки.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_ADDRESS_COORDINATES", in="query", required=false, example="55.831332, 37.451402",
     *         description="Гео-координаты адреса. Необязательный, но если есть возможность - нужно передать.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_DELIVERY_DATE", in="query", required=false, example="24.02.2020",
     *         description="Дата доставки. Параметр обязателен, если в заказе используется курьерская доставка. Формат даты доставки вида d.m.Y",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_DELIVERY_INTERVAL", in="query", required=false, example="18:00 - 19:00",
     *         description="Интервал доставки. Параметр обязателен, если в заказе используется курьерская доставка. Формат интервала обязательно должен быть вида 18:00 - 19:00, иначе будет выброшена ошибка.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_EXACT_TIME", in="query", required=false, example=45,
     *         description="Точное время доставки. Передаеётся, если пользователь хочет доставку в точное время, наприме 45 минут",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200, description="Успешный ответ",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_success"),
     *                 @OA\Schema(
     *                     type="object",
     *                     properties={
     *                         @OA\Property(property="data", type="object",
     *                             properties={
     *                                 @OA\Property(property="TOTAL", type="object", description="Цены, связанные с заказом",
     *                                     properties={
     *                                         @OA\Property(
     *                                             property="DELIVERY_PRICE",
     *                                             type="number",
     *                                             example=600,
     *                                             description="Стоимость доставки"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DELIVERY_PRICE_FORMATED",
     *                                             type="string",
     *                                             example="600 ₽",
     *                                             description="Отформатированная стоимость доставки"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DISCOUNT_PRICE",
     *                                             type="number",
     *                                             example=100,
     *                                             description="Цена скидки"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DISCOUNT_PRICE_FORMATED",
     *                                             type="string",
     *                                             example="100 ₽",
     *                                             description="Отформатированная цена скидки"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DISCOUNT_PRICE_PERCENT",
     *                                             type="number",
     *                                             example=10,
     *                                             description="Процент скидки"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="ORDER_PRICE",
     *                                             type="number",
     *                                             example=2130,
     *                                             description="Стоимость заказа"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="ORDER_PRICE_FORMATED",
     *                                             type="string",
     *                                             example="2130 ₽",
     *                                             description="Отформатированная стоимость заказа"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="AMOUNT_PAYABLE_ORDER",
     *                                             type="number",
     *                                             example=2130,
     *                                             description="Стоимость к оплате"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="AMOUNT_PAYABLE_ORDER_FORMATED",
     *                                             type="string",
     *                                             example="2130 ₽",
     *                                             description="Отформатированная стоимость к оплате"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="ORDER_PRICE_WITHOUT_DISCOUNT",
     *                                             type="number",
     *                                             example=2130,
     *                                             description="Стоимость заказа без скидок"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="ORDER_PRICE_WITHOUT_DISCOUNT_FORMATED",
     *                                             type="string",
     *                                             example="2130 ₽",
     *                                             description="Отформатированная стоимость заказа без скидок"
     *                                         ),
     *                                     }
     *                                 ),
     *                             }
     *                         ),
     *                     }
     *                 )
     *             }
     *         )
     *     ),
     * )
     * 
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return ErrorResponse|SuccessResponse
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $requestParams = $request->getParams();

        try {
            $orderData = $this->orderService->orderCalculate($requestParams);
            return new SuccessResponse($orderData, 200);
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        }
    }
}
