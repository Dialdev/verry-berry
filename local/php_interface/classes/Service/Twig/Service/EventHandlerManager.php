<?php

namespace Natix\Service\Twig\Service;

use Bitrix\Main\EventManager;
use Natix\Service\Core\Bundle\EventHandlerManagerInterface;
use Natix\Service\Twig\Handler\ExtensionHandler;

class EventHandlerManager implements EventHandlerManagerInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    public function handleEvents()
    {
        $this->eventManager->addEventHandler('', 'onAfterTwigTemplateEngineInited', [
            ExtensionHandler::class, 'replaceWithoutRegister'
        ]);
        $this->eventManager->addEventHandler('', 'onAfterTwigTemplateEngineInited', [
            ExtensionHandler::class, 'templateFunction'
        ]);
    }
}
