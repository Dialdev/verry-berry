<?php

namespace Natix\Helpers;

use Natix\Exception\Helpers\EnvironmentHelperException;

/**
 * Хелпер для работы с окружением и конфигурацией
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class EnvironmentHelper
{
    /**
     * key-value хранилище различных конфигурационных констант
     * @var array
     */
    public static $config = [];

    /**
     * @var string
     */
    private static $environment;

    /**
     * Возвращает параметр конфигурации
     * @param string $key
     * @return mixed
     */
    public static function getParam($key)
    {
        return self::$config[$key] ?? null;
    }

    /**
     * Устанавливает параметр конфигурации
     * @param string $key
     * @param mixed $value
     */
    public static function setParam($key, $value)
    {
        self::$config[$key] = $value;
    }

    /**
     * Устанавливает список конфигурационных параметров
     *
     * @param array $config
     * @param bool $isAppend
     */
    public static function setConfiguration(array $config, $isAppend = true)
    {
        if ($isAppend) {
            self::$config = array_merge(self::$config, $config);
        } else {
            self::$config = $config;
        }
    }

    /**
     * Получает тип окружения из файла /.env.php
     *
     * @return bool|string
     * @throws EnvironmentHelperException
     */
    public static function getEnvironmentType()
    {
        if (self::$environment === null) {
            $envFileName = sprintf('%s.env.php', DIRECTORY_SEPARATOR);

            $envFileConfigPath = dirname(__DIR__, 5) . $envFileName;

            $helpInstallMessage = 'Установите нужное окружение, выполнив команду "./vendor/bin/jedi env:init" в консоли';

            if (
                !file_exists($envFileConfigPath)
                || !is_file($envFileConfigPath)
            ) {
                throw new EnvironmentHelperException(
                    sprintf('Файл %s не найден. %s', $envFileName, $helpInstallMessage)
                );
            }

            $envFileConfig = require($envFileConfigPath);

            if (!is_array($envFileConfig)) {
                throw new EnvironmentHelperException(
                    sprintf(
                        'Файл %s должен возвращать массив с параметрами окружения. %s',
                        $envFileName,
                        $helpInstallMessage
                    )
                );
            }

            if (!isset($envFileConfig['ENVIRONMENT'])) {
                throw new EnvironmentHelperException(
                    sprintf('В файле %s не найден параметр ENVIRONMENT. %s', $envFileName, $helpInstallMessage)
                );
            }

            self::$environment = $envFileConfig['ENVIRONMENT'];

        }

        return self::$environment;
    }

    /**
     * @return bool
     * @throws EnvironmentHelperException
     */
    public static function isProduction(): bool
    {
        return (self::getEnvironmentType() === 'prod');
    }

    /**
     * @return bool
     * @throws EnvironmentHelperException
     */
    public static function isDev(): bool
    {
        return (self::getEnvironmentType() === 'dev');
    }
}
