<?php

namespace Natix\Service\EntityProcessingQueue\Service\QueueProcessor;

use Bitrix\Main\Result;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;

/**
 * Результат обработки записи в очереди
 */
class QueueProcessingResult extends Result
{
    /** @var QueueRecord обработанная запись */
    protected $record = null;

    /**
     * @return QueueRecord
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @param QueueRecord $record
     */
    public function setRecord(QueueRecord $record)
    {
        $this->record = $record;
    }
}
