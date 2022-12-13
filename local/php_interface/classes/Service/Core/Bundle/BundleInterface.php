<?php

namespace Natix\Service\Core\Bundle;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;

interface BundleInterface
{
    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container);

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * Возвращает массив объектов эвент-менеджеров бандла
     *
     * Каждый класс должен реализовывать интерфейс \Natix\Service\Core\Bundle\EventHandlerManagerInterface
     *
     * @return EventHandlerManagerInterface[]
     */
    public function registerEventHandlerManagers(): array;

    /**
     *  Запускается единожды в момент создания инстанса бандла
     *
     * @return void
     */
    public function build();

    /**
     * Возвращает массив объектов консольных комманд
     *
     * @return Command[]
     */
    public function registerCommands(): array;
}
