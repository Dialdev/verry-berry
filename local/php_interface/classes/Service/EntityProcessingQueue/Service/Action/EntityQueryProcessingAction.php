<?php

namespace Natix\Service\EntityProcessingQueue\Service\Action;

use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;

/**
 * Интерфейс представляет собой некоторое действие, которое может быть выполнено над сущностью в очереди
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
interface EntityQueryProcessingAction
{
    /**
     * Возвращает код, уникально идентифицирующий тип действия
     * Например complete_order_notification для действия по уведомлению пользователей об выполнении заказа
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Обрабатывает одну запись из очереди
     * При успешной обработке должен возвращать true, при этом запись будет удалена из очереди
     * При ошибке нужно выбрасывать исключение, при этом будет зафиксирована ошибка обработки, запись удалена не будет
     *
     * @param QueueRecord $queueRecord
     *
     * @return bool
     */
    public function process(QueueRecord $queueRecord): bool;
}
