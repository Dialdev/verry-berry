<?php

namespace Natix\Service\Core\Bundle;

interface EventHandlerManagerInterface
{
    /**
     * Навешивание обработчиков событий.
     *
     * Инстанс класса создаётся из конструктора,
     * поэтому \Bitrix\Main\EventManager можно прокинуть в конструкторе класса и в методе handleEvents его заюзать
     *
     * @return void
     */
    public function handleEvents();
}
