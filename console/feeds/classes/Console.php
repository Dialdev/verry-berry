<?php

namespace Classes;

/**
 * Class Console класс для работы с консолью
 *
 * @package Classes
 */
class Console
{
    /**
     * Вывести строку в консоль
     *
     * @param string $string
     * @param bool   $withEmptyLine
     */
    public static function print(string $string, bool $withEmptyLine = false): void
    {
        while (@ob_end_flush()) ;

        if ($withEmptyLine)
            echo PHP_EOL;

        echo trim($string).PHP_EOL;
    }
}
