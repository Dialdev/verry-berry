<?php

namespace Natix\Helpers;

/**
 * Хелпер для работы со строками
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class StringHelper
{
    /**
     * Выбирает подходящую форму слова для количества (1 товар, 2 товара, 5 товаров)
     *
     * @param int $value
     * @param array $forms Список с формами для 1, 2 и 5 объектов
     * @return string
     */
    public static function pluralForm(int $value, array $forms)
    {
        return $value % 10 === 1 && $value % 100 !== 11
            ? $forms[0]
            : ($value % 10 >= 2 && $value % 10 <= 4 && ($value % 100 < 10 || $value % 100 >= 20) ? $forms[1] : $forms[2]);
    }
}
