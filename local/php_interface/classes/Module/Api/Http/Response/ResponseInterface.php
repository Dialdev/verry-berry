<?php

namespace Natix\Module\Api\Http\Response;

/**
 * Интерфейс, который должны реализовывать ответы API
 * @see SuccessResponse
 * @see ErrorResponse
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
interface ResponseInterface
{
    public function toArray(): array;

    public function getHttpStatus(): int;
}
