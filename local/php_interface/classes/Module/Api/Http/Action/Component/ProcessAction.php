<?php

namespace Natix\Module\Api\Http\Action\Component;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Module\Api\Service\Component\ActionService;
use Natix\Module\Api\Slim\Request;
use Natix\Module\Api\Slim\Response;

/**
 * Запрос на выполнение метода компонента, который принимает на вход HttpRequest и возвращает @see ResponseInterface.
 * Вызываемый метод должен быть публичным.
 *
 * Чтобы выполнить метод компонента, необходимо чтобы класс компонента был задан в Natix\Service\Component\ClassMap
 * 
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ProcessAction
{
    /** @var ActionService */
    private $actionService;

    public function __construct(ActionService $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * @OA\Get(
     *     summary="Запрашивает метод компонента",
     *     description="Запрашиваемый метод возвращает какие-либо данные компонента",
     *     path="/api/v1/component/action/{component}/{method}/",
     *     operationId="v1-component-action-get",
     *     tags={"component"},
     *     @OA\Parameter(
     *         name="PARAMS", in="query", required=false, example="",
     *         description="Параметры компонента.",
     *         @OA\Schema(type="object")
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
     *                                     property="html",
     *                                     type="string",
     *                                     description="Строка с html-кодом."
     *                                  ),
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
     * @param array $args
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $componentName = $request->getAttribute('component');
        $method = $request->getAttribute('method');

        try {
            return $this->actionService->executeAction($componentName, $method);
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        }
    }
}
