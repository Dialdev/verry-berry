<?php
namespace Natix\Service\Twig;

use Natix\Service\Core\Bundle\Bundle;
use Natix\Service\Twig\Command\ClearTwigCacheCommand;
use Natix\Service\Twig\Service\EventHandlerManager;

/**
 * Бандл для работы с твигом, содержит события регистрации расширений для твига
 * Команды по работе с твигом
 */
class TwigBundle extends Bundle
{
    public function registerEventHandlerManagers(): array
    {
        return [
            $this->getContainer()->get(EventHandlerManager::class)
        ];
    }

    public function registerCommands(): array
    {
        return [
            new ClearTwigCacheCommand(),
        ];
    }
}
