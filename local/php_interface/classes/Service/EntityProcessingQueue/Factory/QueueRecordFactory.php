<?php

namespace Natix\Service\EntityProcessingQueue\Factory;

use Bitrix\Main\Type\DateTime;
use InvalidArgumentException;
use Natix\Service\EntityProcessingQueue\Service\Action\EntityQueryProcessingAction;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;

/**
 * Фабрика объектов QueueRecord
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class QueueRecordFactory
{
    /**
     * Формирует объект QueueRecord для постановки в очередь
     *
     * @param string $entityId ид сущности (ид заказа, ид товара...) сделано строкой на случай строковых идентификаторов
     * @param EntityQueryProcessingAction $action действие, которое необходимо будет выполнить
     * @param array $entityData данные сущности
     *
     * @throws InvalidArgumentException
     * @return QueueRecord
     */
    public function buildForEnqueue(string $entityId, EntityQueryProcessingAction $action, array $entityData = []): QueueRecord
    {
        if (empty($entityId)) {
            throw new InvalidArgumentException('ИД сущности не может быть пустой');
        }

        $queueRecord = new QueueRecord();

        $queueRecord->setId(0);
        $queueRecord->setEntityId($entityId);
        $queueRecord->setAction($action->getCode());
        $queueRecord->setEntityData($entityData);
        $queueRecord->setEnqueueTime(new DateTime());
        $queueRecord->setIsProcessing(false);
        $queueRecord->setIsError(false);
        $queueRecord->setErrorText('');

        return $queueRecord;
    }
}
