<?php

namespace Natix\Module\Api\Http\Action\Catalog;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\Catalog\SetProduct\Command\SetProductCommand;
use Natix\Module\Api\Service\Catalog\SetProduct\Response\SetProductCommandHandlerResponse;
use Natix\Module\Api\Service\Catalog\SetProduct\SetProductCommandHandler;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Action запроса доступной комбинации букета
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetProductAction
{
    /** @var SetProductCommandHandler */
    private $setProductCommandHandler;
    
    public function __construct(SetProductCommandHandler $setProductCommandHandler)
    {
        $this->setProductCommandHandler = $setProductCommandHandler;
    }

    /**
     * @OA\Get(
     *     summary="Запрашивает доступную комбинацию букета",
     *     description="",
     *     path="/api/v1/catalog/product/set/",
     *     operationId="v1-catalog-product-set",
     *     tags={"catalog"},
     *     @OA\Parameter(
     *         name="sectionId", in="query", required=true, example=18,
     *         description="Идентификатор раздела, в котором находится букет.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="setId", in="query", required=true, example=194,
     *         description="Идентификатор букета.",
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
     *                                 @OA\Property(property="set", type="object", ref="#/components/schemas/set_bouquet"),
     *                                 @OA\Property(
     *                                     property="combinations",
     *                                     type="object",
     *                                     title="Элементы комбинаций букета",
     *                                     properties={
     *                                         @OA\Property(property="sizes", type="array",
     *                                             @OA\Items(
     *                                                 title="Размеры букета",
     *                                                 properties={
     *                                                      @OA\Property(property="id", type="integer", example=2, description="Идентификатор размера"),
     *                                                      @OA\Property(property="name", type="string", example="M", description="Название размера"),
     *                                                      @OA\Property(property="isActive", type="boolean", example="true", description="Признак активности размера в текущей комбинации букета"),
     *                                                      @OA\Property(property="setId", type="integer", example=194, description="Идентификатор букета, для которого доступен данный размер"),
     *                                                      @OA\Property(property="url", type="string", example="/product/chm/", description="Ссылка на букет с данным размером"),
     *                                                      @OA\Property(property="priceDiff", type="number", example=+250, description="Разница в цене букета с данным размером по сравнению с выбранным букетом"),
     *                                                      @OA\Property(property="priceDiffFormat", type="string", example="+250 ₽", description="Отформатированная разница в цене букета с данным размером по сравнению с выбранным букетом"),
     *                                                 }
     *                                             )
     *                                         ),
     *                                         @OA\Property(property="berries", type="array",
     *                                             @OA\Items(
     *                                                 title="Дополнительные ягоды",
     *                                                 properties={
     *                                                      @OA\Property(property="id", type="integer", example=10, description="Идентификатор доп.ягоды"),
     *                                                      @OA\Property(property="name", type="string", example="Малина [M]", description="Название доп.ягоды"),
     *                                                      @OA\Property(property="cardName", type="string", example="Малина", description="Альтернативное название доп.ягоды"),
     *                                                      @OA\Property(property="isActive", type="boolean", example="true", description="Признак активности доп.ягоды в текущей комбинации букета"),
     *                                                      @OA\Property(property="setId", type="integer", example=194, description="Идентификатор букета, для которого доступна данная доп.ягода"),
     *                                                      @OA\Property(property="url", type="string", example="/product/h/", description="Ссылка на букет с данной доп.ягодой"),
     *                                                      @OA\Property(property="image", type="object", title="Картинка доп.ягоды", ref="#/components/schemas/image"),
     *                                                      @OA\Property(property="price", type="object", title="Цена доп.ягоды", ref="#/components/schemas/price"),
     *                                                 }
     *                                             )
     *                                         ),
     *                                         @OA\Property(property="packaging", type="array",
     *                                             @OA\Items(
     *                                                 title="Упаковки букета",
     *                                                 properties={
     *                                                      @OA\Property(property="id", type="integer", example=6, description="Идентификатор упаковки"),
     *                                                      @OA\Property(property="name", type="string", example="Крафт упаковка", description="Название упаковки"),
     *                                                      @OA\Property(property="cardName", type="string", example="Крафт", description="Альтернативное название упаковки"),
     *                                                      @OA\Property(property="isActive", type="boolean", example="true", description="Признак активности упаковки в текущей комбинации букета"),
     *                                                      @OA\Property(property="setId", type="integer", example=194, description="Идентификатор букета, для которого доступна данная упаковка"),
     *                                                      @OA\Property(property="url", type="string", example="/product/om/", description="Ссылка на букет с данной упаковкой"),
     *                                                      @OA\Property(property="image", type="object", title="Картинка упаковки", ref="#/components/schemas/image"),
     *                                                 }
     *                                             )
     *                                         ),
     *                                     }
     *                                 )
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
     *
     * @return ErrorResponse|SuccessResponse
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $requestParams = $request->getParams([
            'sectionId',
            'setId',
        ]);
        
        try {
            $command = SetProductCommand::fromArray($requestParams);
            $result = $this->setProductCommandHandler->handle($command);
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                400
            );
        }
        
        return new SuccessResponse(SetProductCommandHandlerResponse::toState($result), 200);
    }
}
