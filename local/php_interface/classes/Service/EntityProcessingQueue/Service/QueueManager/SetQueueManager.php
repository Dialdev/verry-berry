<?php

namespace Natix\Service\EntityProcessingQueue\Service\QueueManager;

use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;

/**
 * Менеджер очереди для комплектов
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetQueueManager extends BaseQueueManager
{
    public function getType(): string
    {
        return 'set';
    }
    
    public function enqueue(QueueRecord $queueRecord): bool
    {
        $setId = $queueRecord->getEntityId();
        
        if ($setId <= 0) {
            throw new \RuntimeException('Не передан идентификатор комплекта');
        }
        
        return parent::enqueue($queueRecord);
    }
}
