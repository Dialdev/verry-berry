<?php

namespace Natix\Data\Bitrix\Finder;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class AbstractFinder
{
    /**
     * Проверяет - вернулось ли корректное значение из БД,
     * генерирует исключение, если значение не найдено
     * @param int $value
     * @param string $method
     * @throws FinderEmptyValueException
     */
    public function checkValue($value, $method)
    {
        if (!$value) {
            throw new FinderEmptyValueException(sprintf('Не удалось получить значение для метода %s', $method));
        }
    }
}
