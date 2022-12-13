<?php

/** @var \Natix\Service\Core\Bundle\BundleService $bundleService */
$bundleService = \Natix::$container->get(\Natix\Service\Core\Bundle\BundleService::class);

/** @noinspection PhpUnhandledExceptionInspection */
$bundleService->buildCommands();

/**
 * Прокидываем консольные команды всех бандлов в console-jedi
 */
return [
    'commands' => $bundleService->getCommands(),
];
