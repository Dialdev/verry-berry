<?php

namespace Natix\Module\Api\Http\Action\Sale\Order\Basket;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketExtensionService;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action обновления позиции в корзине пользователя
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class UpdateAction
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
     * @OA\Put(
     *     summary="Обновляет количество товара у позиции в корзине",
     *     description="",
     *     path="/api/v1/sale/order/basket/item/{basket_item_id}/",
     *     operationId="v1-sale-order-basket-item-update",
     *     tags={"basket"},
     *     @OA\Parameter(
     *         name="basket_item_id", in="path", required=true, example="4580800",
     *         description="идентификатор позиции товара в корзине",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="QUANTITY", in="query", required=true, example=2,
     *         description="Новое количество товара в корзине",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="GET_PRODUCTS_DATA", in="query", required=false, example="Y",
     *         description="Получить данные товаров. При этом запросе данные товаров не нужны, поэтому рекомендуется передать N",
     *         @OA\Schema(type="string", enum={"Y", "N"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
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
     *                                          example="Не передан ID позиции товара в корзине",
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
     * @return ErrorResponse|SuccessResponse
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            $basketItemId = $this->getAndValidateBasketItemId($args);
            $quantity = $this->getAndValidateQuantity($request);

            $this->basketService->updateBasketItemQuantity($basketItemId, $quantity);

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

    /**
     * Валидирует и возвращает ID позиции в корзине из переданных аргументов запроса
     * @param array $args
     * @return int
     */
    private function getAndValidateBasketItemId(array $args): int
    {
        if (!isset($args['basket_item_id'])) {
            throw new \InvalidArgumentException('Не передан ID позиции товара в корзине');
        }

        if (!is_numeric($args['basket_item_id'])) {
            throw new \InvalidArgumentException('ID позиции товара в корзине должен быть числом');
        }

        $basketItemId = (int)$args['basket_item_id'];

        if ($basketItemId <= 0) {
            throw new \InvalidArgumentException('ID позиции товара в корзине должен быть больше 0');
        }

        return $basketItemId;
    }

    /**
     * Валидирует и возвращает кол-во товара
     * @param Request $request
     * @return int
     */
    private function getAndValidateQuantity(Request $request): int
    {
        $quantity = $request->getParam('QUANTITY');

        if (!is_numeric($quantity)) {
            throw new \InvalidArgumentException('Количество товара должно быть числом');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Количество товара должно быть больше 0');
        }

        return $quantity;
    }
}
