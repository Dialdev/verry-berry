<?php

namespace Natix\Module\Api\Http\Action\Sale\Order;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\OrderService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action создания нового заказа
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SaveAction
{
    /** @var OrderService */
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Post(
     *     summary="Создаёт и сохраняет новый заказ",
     *     description="",
     *     path="/api/v1/sale/order/save/",
     *     operationId="v1-sale-order-save",
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
     *         name="PROPERTY_RECIPIENT_NAME", in="query", required=false, example="Ольга",
     *         description="Имя получателя заказа. В случае, если получателем является сам покупатель, можно ничего не передавать.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_RECIPIENT_PHONE", in="query", required=false, example="+71234567890",
     *         description="Номер телефона получателя заказа. В случае, если получателем является сам покупатель, можно ничего не передавать.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_NEED_POSTCARD", in="query", required=false, example="Y",
     *         description="Бесплатная открытка.",
     *         @OA\Schema(type="string", enum={"Y", "N"})
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_POSTCARD", in="query", required=false, example="Какое нибудь поздравление",
     *         description="Текст открытки, если нужно.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_RECIPIENT_MAKE_PHOTO", in="query", required=false, example="Y",
     *         description="Сделать фото с получателем.",
     *         @OA\Schema(type="string", enum={"Y", "N"})
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_IS_SURPRISE", in="query", required=false, example="Y",
     *         description="Сюрприз, не звонить перед вручением.",
     *         @OA\Schema(type="string", enum={"Y", "N"})
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_ANONYMOUS_ORDER", in="query", required=false, example="Y",
     *         description="Анонимный заказ.",
     *         @OA\Schema(type="string", enum={"Y", "N"})
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
     *         description="Дом/корпус.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_APARTMENT", in="query", required=false, example="303",
     *         description="Квартира/офис. Параметр обязателен, если в заказе используется курьерская доставка и не установлен флажок Узнать адрес у получателя.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_DELIVERY_COMMENT", in="query", required=false, example="Вход со двора",
     *         description="Комментарий к адресу.",
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
     *     @OA\Parameter(
     *         name="PROPERTY_NOT_CALL_CONFIRM", in="query", required=false, example="Y",
     *         description="Не звонить для подтверждения.",
     *         @OA\Schema(type="string", enum={"Y", "N"})
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_SEND_PHOTO", in="query", required=false,
     *         description="Отправить фото перед доставкой.",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 enum={"email", "whatsapp", "telegram"},
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="PROPERTY_COMPANY_NAME", in="query", required=false, example="Рога и Копыта",
     *         description="Название компании.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="FILE_BILL", in="query", required=false, example=276,
     *         description="Идентификатор файла с карточкой компании",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="USE_BONUSPAY",
     *         in="query",
     *         required=false,
     *         example="Y",
     *         description="Флаг оплаты бонусами.",
     *         @OA\Schema(type="string", enum={"Y","N"})
     *     ),
     *     @OA\Parameter(
     *         name="BONUS_PAY",
     *         in="query",
     *         required=false,
     *         example=240,
     *         description="Количество используемых бонусов для оплаты.",
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
     *                          @OA\Property(property="data", type="object",
     *                              properties={
     *                                  @OA\Property(property="ORDER_ID", type="integer", example=3245, description="Номер заказа в случае успешного создания."),
     *                              }
     *                          ),
     *                     }
     *                 )
     *             }
     *         )
     *     ),
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return ErrorResponse|SuccessResponse
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \Natix\Helpers\OrderHelperException
     * @throws \Natix\Module\Api\Exception\Sale\Order\OrderServiceException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function __invoke(Request $request, Response $response)
    {
        $requestParams = $request->getParams();

        try {
            $orderData = $this->orderService->orderSave($requestParams);
            return new SuccessResponse($orderData, 200);
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        }
    }
}
