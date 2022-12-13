<?php

namespace Natix\Module\Api\Http\Action\Sale\Order\Coupon;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\Coupon\BasketCouponService;
use Natix\Module\Api\Slim\Request;
use Natix\Module\Api\Slim\Response;

/**
 * Action удаления купона из применённых у пользователя
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class DeleteAction
{
    /** @var BasketCouponService */
    private $couponService;

    public function __construct(BasketCouponService $couponService)
    {
        $this->couponService = $couponService;
    }
    
    /**
     * @OA\Delete(
     *     summary="Удаляет купон из применённых у пользователя",
     *     description="",
     *     path="/api/v1/sale/order/coupon/{coupon}/",
     *     operationId="v1-sale-order-coupon-delete",
     *     tags={"order/coupon"},
     *     @OA\Parameter(
     *         name="coupon", in="path", required=true, example="CRAFT",
     *         description="Купон, который нужно удалить из примененных",
     *         @OA\Schema(type="string")
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
     *                                 @OA\Property(property="COUPON", type="array",
     *                                     @OA\Items(
     *                                         description="",
     *                                         title="Оставшиеся после удаления применённые купоны",
     *                                         properties={
     *                                             @OA\Property(
     *                                                 property="COUPON",
     *                                                 type="string",
     *                                                 example="CRAFT",
     *                                                 description="Код купона"
     *                                             ),
     *                                             @OA\Property(
     *                                                 property="DESCRIPTION",
     *                                                 type="string",
     *                                                 example="",
     *                                                 description="Описание купона"
     *                                             ),
     *                                             @OA\Property(
     *                                                 property="STATUS",
     *                                                 type="integer",
     *                                                 example=4,
     *                                                 description="Статус купона"
     *                                             ),
     *                                             @OA\Property(
     *                                                 property="STATUS_TEXT",
     *                                                 type="string",
     *                                                 example="применен",
     *                                                 description="Статус купона"
     *                                             ),
     *                                             @OA\Property(
     *                                                 property="APPLY",
     *                                                 type="string",
     *                                                 example="Y",
     *                                                 description="Признак, добавлен ли купон к применённым"
     *                                             ),
     *                                             @OA\Property(
     *                                                 property="DISCOUNT_ID",
     *                                                 type="integer",
     *                                                 example=4,
     *                                                 description="Идентификатор скидки с купоном"
     *                                             ),
     *                                         }
     *                                     )
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
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $args = []): ResponseInterface
    {
        $coupon = !empty($args['coupon']) ? $args['coupon'] : null;
        
        if (!$coupon) {
            return new ErrorResponse(
                new ErrorEntityCollection(
                    [new ErrorEntity('Не передан купон для удаления из применённых')]
                ),
                400
            );
        }
        
        try {
            $this->couponService->deleteCouponFromUserStorage($coupon);
            $data = [
                'COUPON' => $this->couponService->getCouponsFromUserStorage(),
            ];
            return new SuccessResponse($data, 200);
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        }
    }
}
