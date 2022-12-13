<?php

namespace Natix\Service\Core\Bundle;

use Bitrix\Main\EventManager;
use Psr\Container\ContainerInterface;
use Natix\Service\Core\Bundle\Exception\BadInstanceOfException;
use Symfony\Component\Console\Command\Command;

/**
 * Сервис бандлов. Бандл - некая единая функциональности
 *
 * Берёт из конфига EnvironmentHelper::getParam('services')['bundles'] созданные бандлы и инициализирует их:
 * - прокидывает в каждый метод контейнер
 * - билдит каждый бандл (запускает метод build)
 * - запускает метод handleEvents у каждого евент-менеждера, указанного в бандле
 * - отдельно, если нужно умеет билдить консольные команды и отдавать их console-jedi
 *
 * Class BundleService
 * @package Natix\Service\Core\Bundle
 */
class BundleService
{
    /** @var BundleInterface[] */
    private $bundles = [];

    /**
     *
     * @var Command[]
     */
    private $commands = [];

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Региструет объекты бандлов в переменную
     *
     * @param BundleInterface[] $bundles
     * @throws BadInstanceOfException
     */
    public function registerBundles(array $bundles)
    {
        /** @var Bundle $bundle */
        foreach ($bundles as $bundle) {
            if (!($bundle instanceof BundleInterface)) {
                throw new BadInstanceOfException(
                    get_class($bundle),
                    '\Natix\Service\Core\Bundle\BundleInterface'
                );
            }
        }

        $this->bundles = $bundles;
    }

    /**
     * Билд бандлов:
     * - проверят инстансы каждого
     * - пропихивает контейнер
     * - запускает навешивание событий эвент менеджера
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws BadInstanceOfException
     */
    public function build()
    {
        if ($this->booted === false) {
            $this->initializeBundles();

            /** @var BundleInterface $bundle */
            foreach ($this->bundles as $bundle) {
                $bundle->setContainer($this->container);

                $bundle->build();

                $this->registerEventManagerBundle($bundle);
            }

            $this->booted = true;
        }
    }

    /**
     * Билд консольных команд каждого бандла
     *
     * Запускается внутри модуля-болванки natix.consolecommands и подсовывает console-jedi консольные команды
     * всех бандлов
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws BadInstanceOfException
     */
    public function buildCommands()
    {
        $this->commands = [];

        /** @var BundleInterface $bundle */
        foreach ($this->bundles as $bundle) {

            /** @var Command $command */
            foreach ($bundle->registerCommands() as $command) {

                if (!($command instanceof Command)) {
                    throw new BadInstanceOfException(
                        get_class($command),
                        '\Symfony\Component\Console\Command\Command'
                    );
                }

                $this->commands[] = $command;
            }

        }
    }

    /**
     * Возвращает инстанты консольных команд
     *
     * @return Command[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * Проверяет интансы менеджеров событий бандла и запускает метод handleEvents у каждого
     *
     * @param BundleInterface $bundle
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws BadInstanceOfException
     */
    public function registerEventManagerBundle(BundleInterface $bundle)
    {
        /** @var EventHandlerManagerInterface $bundleEventManager */
        foreach ($bundle->registerEventHandlerManagers() as $bundleEventManager) {
            if (!($bundleEventManager instanceof EventHandlerManagerInterface)) {
                throw new BadInstanceOfException(
                    get_class($bundleEventManager),
                    '\Natix\Service\Core\Bundle\EventHandlerManagerInterface'
                );
            }

            $bundleEventManager->handleEvents();
        }
    }

    /**
     * Первоначальная инициализация бандлов
     */
    private function initializeBundles()
    {

    }

    /**
     * @return BundleInterface[]
     */
    public function getBundles(): array
    {
        return $this->bundles;
    }
}
