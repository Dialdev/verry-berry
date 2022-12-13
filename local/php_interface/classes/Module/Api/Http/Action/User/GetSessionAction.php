<?php

namespace Natix\Module\Api\Http\Action\User;

use Natix\Data\Bitrix\UserContainerInterface;
use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Module\Api\Service\User\UserSessionService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Ответит информацией о текущем авторизованном пользователе,
 * либо 401 статус, что пользователь не авторизован
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class GetSessionAction
{
    /**
     * @var UserContainerInterface
     */
    private $userContainer;

    /**
     * @var UserSessionService
     */
    private $userSessionService;

    /**
     *
     * @param UserContainerInterface $userContainer
     */
    public function __construct(UserContainerInterface $userContainer, UserSessionService $userSessionService)
    {
        $this->userContainer = $userContainer;
        $this->userSessionService = $userSessionService;
    }

    /**
     * @OA\Get(
     *     summary="Ответит информацией о текущем авторизованном юзере, либо 401 статус, что юзер не авторизован",
     *     description="",
     *     path="/api/v1/user-session/",
     *     operationId="v1-user-session-get",
     *     tags={"user"},
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
     *                                      @OA\Property(property="SECOND_NAME", type="string", example="Иванович", description="Отчество пользователя"),
     *                                      @OA\Property(property="PERSONAL_PHONE", type="string", example="+7 123 456-78-90", description="Номер телефона пользователя"),
     *                                  )
     *                              }
     *                          ),
     *                     }
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка запроса",
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
     *                                          example="Ошибка доступа",
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
     * @param          $args
     *
     * @return ResponseInterface
     * @throws \Bitrix\Main\ArgumentException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if ($this->userContainer->isAuthorized()) {
            return new SuccessResponse(
                $this->userSessionService->getUserSessionData(),
                200
            );
        }

        return new ErrorResponse(
            new ErrorEntityCollection([new ErrorEntity('пользователь не авторизован')]),
            401
        );
    }
}
