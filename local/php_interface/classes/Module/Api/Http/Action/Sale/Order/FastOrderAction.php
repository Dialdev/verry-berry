<?php

namespace Natix\Module\Api\Http\Action\Sale\Order;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\UseCase\Create\Fast\CreateFastOrderCommand;
use Natix\Module\Api\Service\Sale\Order\UseCase\Create\Fast\CreateFastOrderCommandHandler;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action для создания быстрого заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class FastOrderAction
{
    /** @var CreateFastOrderCommandHandler */
    private $createFastOrderCommandHandler;

    public function __construct(CreateFastOrderCommandHandler $createFastOrderCommandHandler)
    {
        $this->createFastOrderCommandHandler = $createFastOrderCommandHandler;
    }

    /**
     * @OA\Post(
     *     summary="Создаёт и сохраняет новый заказ",
     *     description="",
     *     path="/api/v1/sale/order/saveFast/",
     *     operationId="v1-sale-order-saveFast",
     *     tags={"order"},
     *     @OA\Parameter(
     *         name="LOCATION", in="query", required=true, example="0c5b2444-70a0-4932-980c-b4dc0d3f02b5",
     *         description="Идентификатор местоположения.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="USER_ID", in="query", required=true, example=1,
     *         description="Идентификатор пользователя.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="PERSON_TYPE_ID", in="query", required=true, example=1,
     *         description="Идентификатор типа плательщика. Передаем всегда 1 - физическое лицо.",
     *         @OA\Schema(type="integer")
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
     *         name="PRODUCT_ID", in="query", required=true, example=191,
     *         description="Идентификатор товара для быстрого заказа.",
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
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return ErrorResponse|SuccessResponse
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $requestParams = $request->getParams([
            'LOCATION',
            'USER_ID',
            'PERSON_TYPE_ID',
            'PROPERTY_NAME',
            'PROPERTY_EMAIL',
            'PROPERTY_PERSONAL_PHONE',
            'PRODUCT_ID',
        ]);

        try {
            $command = CreateFastOrderCommand::fromArray($requestParams);
            $result = $this->createFastOrderCommandHandler->handle($command);
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                400
            );
        }

        return new SuccessResponse([
            'ORDER_ID'    => $result->getOrderId(),
            'ORDER_PRICE' => $result->getOrder()->getPrice(),
        ], 200);
    }
}
