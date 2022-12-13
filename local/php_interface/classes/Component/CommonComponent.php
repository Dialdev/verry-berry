<?php

namespace Natix\Component;

use Bex\Bbc\Basis;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Monolog\Registry;
use Natix\Helpers\EnvironmentHelper;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/** @noinspection PhpUnhandledExceptionInspection */
Loader::includeModule('bex.bbc');

abstract class CommonComponent extends Basis
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->logger = Registry::getInstance(EnvironmentHelper::getParam('logger')['base_name']);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer() : ContainerInterface
    {
        return \Natix::$container;
    }

    /**
     * @throws \Bitrix\Main\LoaderException
     */
    protected function loadNeedModules()
    {
        foreach ($this->needModules as $module) {
            Loader::includeModule($module);
        }
    }

    /**
     * Очищает все парамтеры компонента, который начинаются с тильды
     * (битрикс создает их автоматом при каждом вызове)
     *
     *  требуется, чтобы при передачи параметров компонента при ajax запросах - не инвалидировался кеш
     *  (т.к. при каждом повторном вызове битрикс будет плодить новые и новые параметры, повторно экранируя
     *  каждый параметр)
     */
    protected function clearTildaParams()
    {
        foreach ($this->arParams as $key => $param) {
            if ($key{0} === '~') {
                unset($this->arParams[$key]);
            }
        }
    }

    /**
     * Сохраняет текст исключения в лог
     * 
     * @param \Exception $exception
     */
    protected function sendNotifyException($exception)
    {
        $this->logger->error(sprintf(
            'Произошла ошибка в компоненте на странице %s: %s',
            'http://'.SITE_SERVER_NAME.Context::getCurrent()->getRequest()->getRequestedPage(),
            $exception
        ), [
            'func_name' => __METHOD__,
        ]);
    }
}
