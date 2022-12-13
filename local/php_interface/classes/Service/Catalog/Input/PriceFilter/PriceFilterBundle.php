<?php

namespace Natix\Service\Catalog\Input\PriceFilter;

use Natix\Service\Catalog\Input\PriceFilter\Service\EventHandlerManager;
use Natix\Service\Core\Bundle\Bundle;

/**
 * Бандл пользовательского типа свойства "Диапазоны фильтра по цене"
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceFilterBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function registerEventHandlerManagers(): array
    {
        return [
            $this->getContainer()->get(EventHandlerManager::class),
        ];
    }

    /**
     * @inheritDoc
     */
    public function registerCommands(): array
    {
        return [];
    }
}
