<?php

namespace Natix\Module\Api\Http\Action\Sale\Order\Delivery;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\Delivery\DeliveryGroupService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action получения списка доступных доставок для заказа
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class GetAction
{
    /** @var DeliveryGroupService */
    private $deliveryGroupService;
    
    public function __construct(DeliveryGroupService $deliveryGroupService)
    {
        $this->deliveryGroupService = $deliveryGroupService;
    }

    /**
     * @OA\Get(
     *     summary="Получает список доступных доставок для заказа",
     *     description="Службы доставки сгруппированы по типу доставки (курьерская доставка и Самовывоз)",
     *     path="/api/v1/sale/order/delivery/",
     *     operationId="v1-sale-order-delivery-get",
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
     *         name="DELIVERY_ID", in="query", required=false, example=19,
     *         description="Идентификатор службы доставки. Передается в том случае, когда пользователь хочет отредактировать ранее выбранную доставку. В этом случае в параметрах CHECKED доставк будет true.",
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
     *                                 @OA\Property(property="courier", type="object", description="Данные курьерских доставок",
     *                                     properties={
     *                                         @OA\Property(
     *                                             property="TITLE",
     *                                             type="string",
     *                                             example="Курьерская доставка",
     *                                             description=""
     *                                         ),
     *                                         @OA\Property(
     *                                             property="CHECKED",
     *                                             type="string",
     *                                             example="Y",
     *                                             description="Признак того, что ранее был выбран тип доставки - курьерка"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DELIVERY_MIN_PRICE",
     *                                             type="number",
     *                                             example=600,
     *                                             description="Минимальная стоимость курьерской доставки среди всех курьерских доставок"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DELIVERY_MIN_PRICE_FORMAT",
     *                                             type="string",
     *                                             example="600 ₽",
     *                                             description="Отформатированная минимальная стоимость курьерской доставки"
     *                                         ),
     *                                         @OA\Property(property="ROWS", type="array",
     *                                             @OA\Items(
     *                                                 description="",
     *                                                 title="Доступные курьерские доставки",
     *                                                 properties={
     *                                                     @OA\Property(property="ID", type="integer", example=19, description="ИД курьерской доставки"),
     *                                                     @OA\Property(property="NAME", type="string", example="Курьерская доставка в Москве", description="Название курьерской службы доставки"),
     *                                                     @OA\Property(property="CODE", type="string", example="manual_courier", description="Код курьерской службы доставки"),
     *                                                     @OA\Property(property="PRICE", type="number", example=600, description="Стоимость доставки"),
     *                                                 }
     *                                             )
     *                                         )
     *                                     }
     *                                 ),
     *                                 @OA\Property(property="pvz", type="object", description="Данные по самовывозу",
     *                                     properties={
     *                                         @OA\Property(
     *                                             property="TITLE",
     *                                             type="string",
     *                                             example="Самовывоз",
     *                                             description=""
     *                                         ),
     *                                         @OA\Property(
     *                                             property="CHECKED",
     *                                             type="string",
     *                                             example="N",
     *                                             description="Признак того, что ранее был выбран тип доставки - курьерка"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DELIVERY_MIN_PRICE",
     *                                             type="number",
     *                                             example=0,
     *                                             description="Минимальная стоимость самовывоза среди всех пунктов самовывоза"
     *                                         ),
     *                                         @OA\Property(
     *                                             property="DELIVERY_MIN_PRICE_FORMAT",
     *                                             type="string",
     *                                             example="Бесплатно",
     *                                             description="Отформатированная минимальная стоимость самовывоза"
     *                                         ),
     *                                         @OA\Property(property="ROWS", type="array",
     *                                             @OA\Items(
     *                                                 description="",
     *                                                 title="Пункты самовывоза (магазины)",
     *                                                 properties={
     *                                                     @OA\Property(property="ID", type="integer", example=22, description="ИД службы доставки"),
     *                                                     @OA\Property(property="STORE_ID", type="integer", example=1, description="ИД ПВЗ (магазина)"),
     *                                                     @OA\Property(property="TITLE", type="string", example="Магазин в Москве", description="Название ПВЗ (магазина)"),
     *                                                     @OA\Property(property="ADDRESS", type="string", example="Москва, ул. Добролюбова д. 20", description="Адрес ПВЗ (магазина)"),
     *                                                     @OA\Property(property="DESCRIPTION", type="string", example="Описание магазина", description="Описание магазина"),
     *                                                     @OA\Property(property="SCHEDULE", type="string", example="ПН - ПТ 09:00 - 18:00", description="График работы"),
     *                                                     @OA\Property(property="GPS_N", type="string", example="55.8174248", description="GPS широта"),
     *                                                     @OA\Property(property="GPS_S", type="string", example="37.5930955", description="GPS долгота"),
     *                                                     @OA\Property(property="PHONE", type="string", example="+7 (925) 437-49-22", description="Телефон"),
     *                                                     @OA\Property(property="EMAIL", type="string", example="veryberry@gmail.com", description="Email"),
     *                                                     @OA\Property(property="PRICE", type="number", example=0, description="Стоимость доставки"),
     *                                                 }
     *                                             )
     *                                         )
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
     * @param array $args
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
                $this->deliveryGroupService->getDeliveryByGroup($requestParams),
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
