<?php

namespace Natix\Service\Catalog\Bouquets;

use Natix\Service\Catalog\Bouquets\Command\BouquetPriceRecalculateCommand;
use Natix\Service\Catalog\Bouquets\Service\EventHandlerManager;
use Natix\Service\Core\Bundle\Bundle;

/**
 * Бандл сервиса для работы с букетами
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BouquetsBundle extends Bundle
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
        return [
            $this->getContainer()->get(BouquetPriceRecalculateCommand::class),
        ];
    }
}
