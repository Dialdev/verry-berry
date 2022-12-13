<?php

require_once __DIR__.'/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    $file = realpath(__DIR__.'/../').DIRECTORY_SEPARATOR.$class.'.php';

    $file = str_replace('\\', '/', $file);

    is_file($file) and require_once $file;
});
