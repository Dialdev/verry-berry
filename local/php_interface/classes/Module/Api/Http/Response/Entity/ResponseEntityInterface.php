<?php

namespace Natix\Module\Api\Http\Response\Entity;

/**
 * Интерфейс DTO объекта в ответе API
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
interface ResponseEntityInterface
{
    /**
     * Возвращает сущность приведенную к массиву
     * @return array
     */
    public function toArray(): array;
}
