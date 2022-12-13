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
 * Action авторизация пользователя
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PostSessionAction
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
     *     summary="Авторизирует пользователя на сайте",
     *     description="",
     *     path="/api/v1/user-session/",
     *     operationId="v1-user-session-authorize",
     *     tags={"user"},
     *     @OA\Parameter(
     *         name="LOGIN", in="query", required=true, example="ivan@gmail.com",
     *         description="Логин пользователя (email)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="PASSWORD", in="query", required=true, example="",
     *         description="Пароль",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="REMEMBER", in="query", required=false, example="Y",
     *         description="Запомнить пользователя (по умолчанию Y)",
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
     *                                  @OA\Property(property="USER", type="object",
     *                                      @OA\Property(property="ID", type="integer", example=8, description="идентификатор пользователя"),
     *                                      @OA\Property(property="EMAIL", type="string", example="ivan@gmail.com", description="E-mail пользователя"),
     *                                      @OA\Property(property="NAME", type="string", example="Иван", description="Имя пользователя"),
     *                                      @OA\Property(property="LAST_NAME", type="string", example="Иванов", description="Фамилия пользователя"),
     *                                      @OA\Property(property="PERSONAL_PHONE", type="string", example="+7 123 456-78-90", description="Номер телефона пользователя"),
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
     * @return ResponseInterface
     * @throws \Natix\Module\Api\Exception\User\UserServiceException
     * @throws \Natix\Service\Tools\Data\PhoneNumber\PhoneNumberServiceException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $requestParams = $request->getParams([
            'LOGIN',
            'PASSWORD',
            'REMEMBER',
        ]);

        try {
            return new SuccessResponse(
                $this->userService->login($requestParams),
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
