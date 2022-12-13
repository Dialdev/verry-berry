<?php

namespace Natix\Module\Api\Http\Action\Sale\Order\PaySystem;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\PaySystem\PaySystemsOrder;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action получения списка доступных платежных систем
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class GetAction
{
    /** @var PaySystemsOrder */
    private $paySystemOrder;
    
    public function __construct(PaySystemsOrder $paySystemOrder)
    {
        $this->paySystemOrder = $paySystemOrder;
    }

    /**
     * @OA\Get(
     *     summary="Получает список доступных платёжных систем для заказа",
     *     description="",
     *     path="/api/v1/sale/order/paysystem/",
     *     operationId="v1-sale-order-paysystem-get",
     *     tags={"order"},
     *     @OA\Parameter(
     *         name="LOCATION", in="query", required=true, example="0c5b2444-70a0-4932-980c-b4dc0d3f02b5",
     *         description="Идентификатор местоположения.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PERSON_TYPE_ID", in="query", required=true, example=1,
     *         description="Идентификатор типа плательщика. Передаем всегда 1 - физическое лицо.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="DELIVERY_ID", in="query", required=true, example=19,
     *         description="Идентификатор выбранной службы доставки",
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
     *                                 @OA\Property(property="PAY_SYSTEM", type="array",
     *                                     @OA\Items(
     *                                         description="",
     *                                         title="Доступные для заказа платёжные системы",
     *                                         properties={
     *                                             @OA\Property(property="ID", type="integer", example=2, description="ИД платёжной системы"),
     *                                             @OA\Property(property="NAME", type="string", example="Наличный расчет", description="Название платёжной системы"),
     *                                             @OA\Property(property="CODE", type="string", example="", description="Код платёжной системы"),
     *                                             @OA\Property(property="DESCRIPTION", type="string", example="", description="Описание платёжной системы"),
     *                                         }
     *                                     )
     *                                 ),
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
        $requestParams = $request->getParams([
            'LOCATION',
            'PERSON_TYPE_ID',
            'USER_ID',
            'DELIVERY_ID',
        ]);

        try {
            return new SuccessResponse(
                $this->paySystemOrder->getPaySystemsAndTotal($requestParams),
                200
            );
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        }
    }
}
