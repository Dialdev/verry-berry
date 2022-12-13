<?php
return array (
  'utf_mode' =>
  array (
    'value' => true,
    'readonly' => true,
  ),
  'cache_flags' =>
  array (
    'value' =>
    array (
      'config_options' => 3600.0,
      'site_domain' => 3600.0,
    ),
    'readonly' => false,
  ),
  'cookies' =>
  array (
    'value' =>
    array (
      'secure' => false,
      'http_only' => true,
    ),
    'readonly' => false,
  ),
  'exception_handling' =>
  array (
    'value' =>
    array (
      'debug' => true,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' => NULL,
    ),
    'readonly' => false,
  ),
  'connections' =>
  array (
    'value' =>
    array (
      'default' =>
      array (
        'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
        'host' => '127.0.0.1:3310',
        'database' => 'u1149019_veryberrylab',
        'login' => 'u1149019_veryber',
        'password' => 'F7a8N1s6',
        'options' => 2.0,
      ),
    ),
    'readonly' => true,
  ),
  'crypto' =>
  array (
    'value' =>
    array (
      'crypto_key' => '15bee433ee8dcf93c192295c6c1943ca',
    ),
    'readonly' => true,
  ),
  'monolog' =>
  array (
    'value' =>
    array (
      'handlers' =>
      array (
        'default' =>
        array (
          'class' => '\\Monolog\\Handler\\RotatingFileHandler',
          'level' => 'DEBUG',
          'maxFiles' => 14,
          'filename' => dirname($_SERVER['DOCUMENT_ROOT']) . '/.logs/app-berry.log',
        ),
      ),
      'loggers' =>
      array (
        'app' =>
        array (
          'handlers' =>
          array (
            0 => 'default',
          ),
        ),
      ),
    ),
    'readonly' => false,
  ),
);