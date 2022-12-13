<?php

namespace Natix\Module\Api\Slim;

use Natix\Helpers\EnvironmentHelper;
use Natix\Natix\Container;
use Slim\DefaultServicesProvider;
use Zend\ServiceManager\ServiceManager;

class ApiContainer
{
    /**
     * Собираем контейнер на замену слимовскому со своим блекджеком и ..
     *
     * @param ServiceManager $container
     */
    public function build(Container $container)
    {
        // {{{
        /**
         * \EnvironmentHelper::getParam('debug') раньше не получить. Приходится получать его тут
         */
        $containerServiceSettings = $container->get('settings');

        $containerServiceSettings['displayErrorDetails'] = EnvironmentHelper::getParam('debug');

        $containerServiceSettings['debug'] = EnvironmentHelper::getParam('debug');

        $container->setAllowOverride(true);

        $container->setService('settings', $containerServiceSettings);

        $container->setAllowOverride(false);
        // }}}

        $defaultServiceProvider = new DefaultServicesProvider();

        /** @noinspection PhpParamsInspection */
        $defaultServiceProvider->register($container);
    }
}
