<?php

namespace Natix\Module\Api\Http\Action\File;

use Natix\Module\Api\Http\Response\Entity\ErrorEntity;
use Natix\Module\Api\Http\Response\Entity\ErrorEntityCollection;
use Natix\Module\Api\Http\Response\ErrorResponse;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

/**
 * Action загрузки файлов на сервер
 *
 * @link https://redmine.book24.ru/issues/31258
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class UploadAction
{
    private $savePath = 'api/file/upload';

    /**
     * @OA\Post(
     *     summary="Загружает один файл на сервер",
     *     description="",
     *     path="/api/v1/file/upload/",
     *     operationId="v1-file-upload",
     *     tags={"file"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(
     *                         @OA\Property(
     *                             description="Изображение",
     *                             property="file",
     *                             type="string",
     *                             format="binary"
     *                         )
     *                     )
     *                 }
     *             )
     *         )
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
     *                                      property="id",
     *                                      type="integer",
     *                                      example=276,
     *                                      description="Идентификатор файла"
     *                                  ),
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
     *
     * @return ErrorResponse|SuccessResponse
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            if (!$_FILES['file']) {
                throw new \InvalidArgumentException('Не передан файл');
            }

            $fileId = \CFile::SaveFile($_FILES['file'], $this->savePath);
            
            return new SuccessResponse([
                'id' => $fileId,
                'file' => \CFile::GetFileArray($fileId),
            ], 200);
        }  catch (\InvalidArgumentException $exception) {
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
