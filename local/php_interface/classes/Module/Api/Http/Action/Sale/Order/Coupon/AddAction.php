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
 * Action добавления купона к пользователю к применённым
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class AddAction
{
    /** @var BasketCouponService */
    private $couponService;
    
    public function __construct(BasketCouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * @OA\Post(
     *     summary="Добавляет купон к пользователю к применённым",
     *     description="",
     *     path="/api/v1/sale/order/coupon/",
     *     operationId="v1-sale-order-coupon-add",
     *     tags={"order/coupon"},
     *     @OA\Parameter(
     *         name="COUPON", in="query", required=true, example="CRAFT",
     *         description="Купон",
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
     *                                         title="Примененный купоны пользователя",
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
     * @throws \Natix\Module\Api\Exception\Sale\Order\Coupon\CouponServiceException
     * @throws \Natix\Service\Sale\Coupon\Exception\CouponApplyException
     * @throws \Natix\Service\Sale\Coupon\Exception\CouponApplyExpiredException
     * @throws \Quetzal\Service\Sale\Coupon\Exception\Service\DeleteCouponException
     * @throws \Exception
     */
    public function __invoke(Request $request, Response $response, $args = [])
    {
        $coupon = $request->getParam('COUPON');

        try {
            $this->couponService->addCouponInUserStorage($coupon);
    
            /**
             * На сайте может быть введен только один купон
             * Поэтому удаляем все купоны, кроме того, который пользователь ввел последним
             */
            $allCoupons = $this->couponService->getCouponsFromUserStorage();
            foreach ($allCoupons as $couponFromStorage) {
                $couponCode = $couponFromStorage['COUPON'];
                if (strtoupper($couponCode) !== strtoupper($coupon)) {
                    $this->couponService->deleteCouponFromUserStorage($couponCode);
                }
            }
    
            $data = [
                'COUPON' => $this->couponService->getApplyCoupons(),
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
