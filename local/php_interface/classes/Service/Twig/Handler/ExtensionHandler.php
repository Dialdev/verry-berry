<?php

namespace Natix\Service\Twig\Handler;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Natix\Service\Twig\Extension\StringFunction;
use Natix\Service\Twig\Extension\TemplateExtension;
use Psr\Log\LoggerInterface;

class ExtensionHandler
{
    public static function replaceWithoutRegister(Event $event)
    {
        /** @var \Twig_Environment $engine */
        $engine = $event->getParameter(0);
        if (!$engine instanceof \Twig_Environment) {
            $logger = \Natix::$container->get(LoggerInterface::class);
            $logger->error(
                'Ошибка при регистрации расширений твига - не найден объект Twig_Environment',
                ['func_name' => __METHOD__]
            );
            return false;
        }
        $engine->addExtension(new StringFunction());
        return new EventResult(EventResult::SUCCESS, [$engine]);
    }

    public static function templateFunction(Event $event)
    {
        /** @var \Twig_Environment $engine */
        $engine = $event->getParameter(0);
        if (!$engine instanceof \Twig_Environment) {
            $logger = \Natix::$container->get(LoggerInterface::class);
            $logger->error(
                'Ошибка при регистрации расширений твига - не найден объект Twig_Environment',
                ['func_name' => __METHOD__]
            );
            return false;
        }
        $engine->addExtension(new TemplateExtension());
        return new EventResult(EventResult::SUCCESS, [$engine]);
    }
}
