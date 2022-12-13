<?php

namespace Natix\Service\EntityProcessingQueue\Service\QueueProcessor;

use Natix\Service\EntityProcessingQueue\Service\QueueManager\QueueManager;

/**
 * Интерфейс процессора очереди, то есть некого объекта,
 * который может взять запись из очереди и выполнить над ней действие
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
interface QueueProcessor
{
    /**
     * Выполняет действие $action над одной записью очереди
     * @param int $strategy Порядок выбора записей (FIFO, LIFO)
     * @return QueueProcessingResult
     */
    public function processOneFromQueue(int $strategy = QueueManager::STRATEGY_FIFO): QueueProcessingResult;
}
