<?php

namespace Natix\Module\Api\Http\Action\Catalog;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Catalog\ProductsFromOrderService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action получения списка товаров для вывода на странице оформления заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class GetProductsFromOrderAction
{
    /** @var ProductsFromOrderService */
    private $productsFromOrderService;
    
    public function __construct(ProductsFromOrderService $productsFromOrderService)
    {
        $this->productsFromOrderService = $productsFromOrderService;
    }

    /**
     * @OA\Get(
     *     summary="Получает список товаров для вывода на странице оформления заказа",
     *     description="Дополнительно проверяется состав текущей корзины. Если в корзине уже есть товар, то в этом списке его не будет",
     *     path="/api/v1/catalog/products/from_order/",
     *     operationId="v1-catalog-products-from-order",
     *     tags={"catalog"},
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
     *                                  @OA\Property(property="products", type="array",
     *                                      @OA\Items(
     *                                          description="",
     *                                          title="Список товаров",
     *                                          properties={
     *                                              @OA\Property(property="ID", type="integer", example=165, description="Идентификатор товара"),
     *                                              @OA\Property(property="NAME", type="string", example="Шляпная коробка №1", description="Название товара"),
     *                                              @OA\Property(property="CODE", type="string", example="hlyapnaya-korobka-1", description="Символьный код товара"),
     *                                              @OA\Property(property="URL", type="string", example="/product/shlyapnaya-korobka-1/", description="Ссылка на карточку товара"),
     *                                              @OA\Property(property="IBLOCK_SECTION_ID", type="integer", example=14, description="Идентификатор раздела"),
     *                                              @OA\Property(property="PREVIEW_PICTURE", type="integer", example=174, description="Идентификатор картинки"),
     *                                              @OA\Property(property="IMAGE", type="object", ref="#/components/schemas/image"),
     *                                              @OA\Property(property="PRICES", ref="#/components/schemas/price"),
     *                                              @OA\Property(property="SIZE", ref="#/components/schemas/size"),
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
            $products = $this->productsFromOrderService->getProducts();

            return new SuccessResponse([
                'products' => $products,
            ], 200);
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
