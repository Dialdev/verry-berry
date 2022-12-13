<?php

namespace Natix\Service\Core\Bundle;

use Psr\Container\ContainerInterface;

abstract class Bundle implements BundleInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @return void
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     *  Запускается единожды в момент создания инстанса бандла
     *
     * @return void
     */
    public function build()
    {

    }
}
