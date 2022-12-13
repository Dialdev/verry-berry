<?php

namespace Natix\Service\Catalog\Bouquets\Service;

use Bitrix\Main\EventManager;
use Natix\Service\Core\Bundle\EventHandlerManagerInterface;

/**
 * Менеджер событий сервиса для работы с букетами
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class EventHandlerManager implements EventHandlerManagerInterface
{
    /** @var EventManager */
    private $eventManager;
    
    /** @var Handler */
    private $handler;
    
    public function __construct(EventManager $eventManager, Handler $handler)
    {
        $this->eventManager = $eventManager;
        $this->handler = $handler;
    }

    public function handleEvents()
    {
        $this->eventManager->addEventHandler(
            'catalog',
            '\Bitrix\Catalog\Price::OnAfterAdd',
            [$this->handler, 'bouquetPriceRecalculate']
        );

        $this->eventManager->addEventHandler(
            'catalog',
            '\Bitrix\Catalog\Price::OnAfterUpdate',
            [$this->handler, 'bouquetPriceRecalculate']
        );
        
        $this->eventManager->addEventHandler(
            'catalog',
            'OnProductSetAdd',
            [$this->handler, 'bouquetPriceRecalculateBySetAdd']
        );

        $this->eventManager->addEventHandler(
            'catalog',
            'OnProductSetUpdate',
            [$this->handler, 'bouquetPriceRecalculateBySetUpdate']
        );

        $this->eventManager->addEventHandler(
            'catalog',
            'OnProductSetDelete',
            [$this->handler, 'bouquetPriceRecalculateBySetDelete']
        );
    }
}
