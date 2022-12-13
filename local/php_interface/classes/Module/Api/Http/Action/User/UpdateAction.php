<?php

namespace Natix\Module\Api\Http\Action\User;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\User\UserService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Экшн обновления данных пользователя
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class UpdateAction
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Put(
     *     summary="Обновляет данные пользователя",
     *     description="Обновляет данные пользователя и его профиль в интернет магазине",
     *     path="/api/v1/user/",
     *     operationId="v1-user-update",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="EMAIL", in="query", required=true, example="ivan@gmail.com",
     *         description="E-mail пользователя",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="NAME", in="query", required=true, example="Иван",
     *         description="Имя пользователя",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="LAST_NAME", in="query", required=false, example="Иванов",
     *         description="Фамилия пользователя",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PERSONAL_PHONE", in="query", required=true, example="+71234567890",
     *         description="Номер телефона",
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
     *                          @OA\Property(property="data", type="object",
     *                              properties={
     *                                  @OA\Property(property="USER_ID", type="integer", example=8, description="идентификатор пользователя"),
     *                                  @OA\Property(property="USER_PROFILE_ID", type="integer", example=3, description="идентификатор профиля пользователя")
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
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $requestParams = $request->getParams();

        try {
            return new SuccessResponse(
                $this->userService->updateUser($requestParams),
                200
            );
        } catch (\InvalidArgumentException $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        } catch (\Exception $exception) {
            return new ErrorResponse(
                new ErrorEntityCollection([new ErrorEntity($exception->getMessage())]),
                200
            );
        }
    }
}
