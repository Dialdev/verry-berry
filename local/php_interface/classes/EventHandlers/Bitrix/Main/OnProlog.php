<?php

namespace Natix\EventHandlers\Bitrix\Main;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Cookie;
use Maximaster\Tools\Events\BaseEvent;
use Natix\Data\Bitrix\UserContainerInterface;

/**
 * Обработчик событий, вызываемых в начале визуальной части пролога сайта.
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class OnProlog extends BaseEvent
{
    /**
     * Имя куки, в которой хранится id партнера, за которым закреплен пользователь
     */
    const PARTNER_ID_COOKIE_NAME = 'BERRY_PARTNER_ID';

    /**
     * Обработчик события перехода по партнерской ссылке.
     * Сохраняет идентификатор партнера в куках пользователя
     */
    public static function partnershipHandler()
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();
        /** @var UserContainerInterface $userContainer */
        $userContainer = \Natix::$container->get(UserContainerInterface::class);
        
        if (
            !$request->isAjaxRequest()
            && $partnerId = filter_var($request->getQuery('partnerId'), FILTER_SANITIZE_NUMBER_INT)
        ) {
            if ($userContainer->isAuthorized() && $userContainer->getId() == $partnerId) {
                return;
            }

            $cookie = new Cookie(
                self::PARTNER_ID_COOKIE_NAME,
                $partnerId,
                time() + 2592000
            );
            $cookie->setPath('/');
            $cookie->setDomain($context->getServer()->getHttpHost());
            $context->getResponse()->addCookie($cookie);

            // При установке cookie может получиться ситуация, что уже начат вывод,
            // и получится 'отложенная установка' (до следующей загрузки страницы).
            // Для правильной работы нужно явно добавить значения в окружение
            $prefix = Option::get('main', 'cookie_name', 'BITRIX_SM') . '_';
            $_COOKIE[$prefix . self::PARTNER_ID_COOKIE_NAME] = $partnerId;
            $_REQUEST[$prefix . self::PARTNER_ID_COOKIE_NAME] = $partnerId;
        }
    }
}
