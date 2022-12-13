<?php

namespace Natix\Service\EntityProcessingQueue\Service\QueueManager;

use Natix\Service\EntityProcessingQueue\Service\Action\EntityQueryProcessingAction;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;

/**
 * Интерфейс менеджера очередей
 * Менеджер очереди - это объект, который выполняет базовые операции с очередью (постановка в очередь, получение из очереди...)
 */
interface QueueManager
{
    /** Первые записи обрабатываются первыми */
    const STRATEGY_FIFO = 1;

    /** Последние записи обрабатываются первыми */
    const STRATEGY_LIFO = 2;

    /**
     * Возвращает уникальный код типа записи в очереди
     * Например order для заказа, product для товара
     * @return string
     */
    public function getType(): string;

    /**
     * Записывает сущность в очередь на обработку
     * @param QueueRecord $record
     * @return bool
     */
    public function enqueue(QueueRecord $record): bool;

    /**
     * Получает одну запись из очереди над которой необходимо выполнить действие $action
     * @param EntityQueryProcessingAction $action
     * @param int $strategy
     * @return QueueRecord|null
     */
    public function getOneFromQueue(EntityQueryProcessingAction $action, int $strategy = self::STRATEGY_FIFO);

    /**
     * Удаляет запись из очереди
     * @param QueueRecord $record
     * @return bool
     */
    public function removeFromQueue(QueueRecord $record): bool;

    /**
     * Устанавливает флаг ошибки для записи
     * @param QueueRecord $record
     * @param string $errorMessage
     * @return mixed
     */
    public function setError(QueueRecord $record, string $errorMessage);

    /**
     * Устанавливает признак того, что запись находится в обработке
     * @param QueueRecord $record
     * @param bool $isProcessing находится ли запись в обработке
     * @return bool
     */
    public function setProcessingStatus(QueueRecord $record, bool $isProcessing = true): bool;
}
