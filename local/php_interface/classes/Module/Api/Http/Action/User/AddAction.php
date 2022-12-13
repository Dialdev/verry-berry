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
 * Экшн добавления нового пользователя на сайт (регистрация)
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class AddAction
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
     * @OA\Post(
     *     summary="Регистрирует нового пользователя",
     *     description="Регистрирует нового пользователя. В случае, если не передан параметр AUTHORIZE или AUTHORIZE не равен N, то сразу авторизирует пользователя",
     *     path="/api/v1/user/",
     *     operationId="v1-user-add",
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
     *         name="PERSONAL_PHONE", in="query", required=true, example="+71234567890",
     *         description="Номер телефона пользователя",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="LAST_NAME", in="query", required=false, example="Иванов",
     *         description="Фамилия пользователя",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PASSWORD", in="query", required=true, example="",
     *         description="Пароль",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="CONFIRM_PASSWORD", in="query", required=true, example="",
     *         description="Подтверждение пароля",
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
     *                                  title="Пользователь",
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
     * @return ResponseInterface
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Natix\Module\Api\Exception\User\UserAuthorizationException
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $requestParams = $request->getParams();

        try {
            return new SuccessResponse(
                $this->userService->addUser($requestParams),
                200
            );
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
