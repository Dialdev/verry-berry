<?php

namespace Natix\Module\Api\Http\Action\Sale\Order\Basket;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketExtensionService;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action получения текущей корзины пользователя
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class GetAction
{
    /**
     * @var BasketService
     */
    private $basketService;

    /**
     * @var BasketExtensionService
     */
    private $basketExtensionService;

    public function __construct(BasketService $basketService, BasketExtensionService $basketExtensionService)
    {
        $this->basketService = $basketService;
        $this->basketExtensionService = $basketExtensionService;
    }

    /**
     * @OA\Get(
     *     summary="Получает текущуую корзину пользователя",
     *     description="Получает текущуую корзину пользователя. По умолчанию данные товаров (размер, цвет упаковки и т.д.) не отдаются. Для того, чтобы получить эту информацию нужно передать GET параметр GET_PRODUCTS_DATA=Y.",
     *     path="/api/v1/sale/order/basket/",
     *     operationId="v1-sale-order-basket-get",
     *     tags={"basket"},
     *     @OA\Parameter(
     *         name="GET_PRODUCTS_DATA", in="query", required=false, example="Y",
     *         description="Получить данные товаров",
     *         @OA\Schema(type="string", enum={"Y", "N"})
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
     *                                  @OA\Property(property="BASKET_ITEMS", type="array",
     *                                      @OA\Items(
     *                                          description="",
     *                                          title="Позиции в корзине",
     *                                          properties={
     *                                              @OA\Property(property="ID", type="integer", example=7, description="ИД позиции товара в корзине"),
     *                                              @OA\Property(property="PRODUCT_ID", type="integer", example=25, description="ИД товара"),
     *                                              @OA\Property(property="BASE_PRICE", type="float", example=980, description="Базовая цена"),
     *                                              @OA\Property(property="BASE_PRICE_FORMATTED", type="string", example="980 ₽", description="Отформатированная базовая цена"),
     *                                              @OA\Property(property="PRICE", type="float", example=882, description="Цена со скидкой (по которой фактически продается товар)"),
     *                                              @OA\Property(property="PRICE_FORMATTED", type="string", example="882 ₽", description="Отформатированная цена"),
     *                                              @OA\Property(property="DISCOUNT_PERCENT", type="integer", example=10, description="Процент скидки"),
     *                                              @OA\Property(property="SUM", type="float", example=1764, description="Сумма позиции (кол-во товара * цена товара)"),
     *                                              @OA\Property(property="SUM_FORMATTED", type="string", example="1764 ₽", description="Отформатированная сумма позиции"),
     *                                              @OA\Property(property="DISCOUNT_PRICE", type="float", example=98, description="Цена скидки"),
     *                                              @OA\Property(property="DETAIL_PAGE_URL", type="string", example="/product/buket-s-klubnikoy-razmer-s-g-chernyy/", description="Ссылка на карточку товара"),
     *                                              @OA\Property(property="QUANTITY", type="integer", example=2, description="Количество товара"),
     *                                              @OA\Property(property="WEIGHT", type="integer", example="850", description="Вес товара (грамм)"),
     *                                              @OA\Property(property="PRODUCT_DATA", type="object", ref="#/components/schemas/set_bouquet")
     *                                          }
     *                                      )
     *                                  )
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
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            $basketData = $this->basketService->getCurUserPreparedBasket();

            $basketData = $this->basketExtensionService->extendBasketIfRequired($basketData, $request);

            return new SuccessResponse($basketData, 200);
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
