<?php

namespace Natix\Service\Catalog\Input\PriceFilter\Service;

use Bitrix\Main\EventManager;
use Natix\Service\Catalog\Input\PriceFilter\Handler\PriceFilterInput;
use Natix\Service\Core\Bundle\EventHandlerManagerInterface;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class EventHandlerManager implements EventHandlerManagerInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var PriceFilterInput
     */
    private $priceFilterInput;

    public function __construct(EventManager $eventManager, PriceFilterInput $priceFilterInput)
    {
        $this->eventManager = $eventManager;
        $this->priceFilterInput = $priceFilterInput;
    }

    /**
     * @inheritDoc
     */
    public function handleEvents()
    {
        $this->eventManager->addEventHandler('main', 'OnUserTypeBuildList', [
            $this->priceFilterInput, 'getUserTypeDescription'
        ]);
    }
}
