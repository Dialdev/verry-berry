<?php

namespace classes;

/**
 * Class _Base базовый класс остальных классов
 *
 * @package classes
 */
abstract class _Base
{
    protected static array $config = [];

    public function __construct()
    {
        if (!self::$config) {
            self::$config = require __DIR__.'/../configs/main.php';

            $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__.'/../../../');

            require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

            \CModule::IncludeModule('sale');
        }
    }

    /**
     * Вывести строку в консоль
     *
     * @param string $string
     * @param bool   $withEmptyLine
     */
    protected static function prints(string $string, bool $withEmptyLine = false): void
    {
        Console::print($string, $withEmptyLine);
    }
}
