<?php

namespace Natix\Module\Api\Http\Action\Sale\Order\Bonus;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\Bonus\BonusService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action получения доступных бонусов для оплаты заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class GetAction
{
    /** @var BonusService */
    private $bonusService;
    
    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }

    /**
     * @OA\Get(
     *     summary="Возвращает информацию по доступным бонусам пользователя",
     *     description="",
     *     path="/api/v1/sale/order/bonus/",
     *     operationId="v1-sale-order-bonus",
     *     tags={"order"},
     *     @OA\Parameter(
     *         name="USER_ID", in="query", required=true, example=25,
     *         description="Идентификатор пользователя, для которого следует получить информацию по бонусам",
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
     *                                  @OA\Property(
     *                                      property="ID",
     *                                      type="integer",
     *                                      example=7,
     *                                      description="Идентификатор счёта"
     *                                  ),
     *                                  @OA\Property(
     *                                      property="USER_ID",
     *                                      type="integer",
     *                                      example=25,
     *                                      description="Идентификатор пользователя"
     *                                  ),
     *                                  @OA\Property(
     *                                      property="CURRENT_BUDGET",
     *                                      type="number",
     *                                      example=152,
     *                                      description="Текущий бюджет пользователя"
     *                                  ),
     *                                  @OA\Property(
     *                                      property="LOCKED",
     *                                      type="string",
     *                                      example="N",
     *                                      description="Признак заблокированности счёта"
     *                                  ),
     *                                  @OA\Property(
     *                                      property="DATE_LOCKED",
     *                                      type="string",
     *                                      example="25.04.2020",
     *                                      description="Дата блокировки счёта"
     *                                  ),
     *                              }
     *                          ),
     *                     }
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка входных данных",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/response_error_v2"),
     *                 @OA\Schema(
     *                     type="object",
     *                     properties={
     *                         @OA\Property(property="errors", type="array",
     *                             @OA\Items(
     *                                  description="Список ошибок",
     *                                  title="Корзина",
     *                                  properties={
     *                                      @OA\Property(
     *                                          property="message",
     *                                          type="string",
     *                                          example="Пользователь уже зарегистрирован",
     *                                          description="Текст ошибки"
     *                                      ),
     *                                  }
     *                             )
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
     *
     * @return ErrorResponse|SuccessResponse
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            $userId = (int)$request->getParam('USER_ID');
            $data = $this->bonusService->getUserBonuses($userId);
            return new SuccessResponse($data, 200);
        } catch (\InvalidArgumentException $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                400
            );
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        }
    }
}
