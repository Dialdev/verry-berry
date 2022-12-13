<?php

namespace Natix\Service\EntityProcessingQueue\Service\QueueManager;

use Natix\Service\EntityProcessingQueue\Service\Action\EntityQueryProcessingAction;
use Natix\Service\EntityProcessingQueue\Exception\QueueDuplicateRecordException;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;
use Natix\Service\EntityProcessingQueue\Repository\QueueRecordRepository;

/**
 * Базовый менеджер очереди
 * Хранит очередь в таблице БД
 */
abstract class BaseQueueManager implements QueueManager
{
    /** @var QueueRecordRepository  */
    private $repository;

    abstract public function getType(): string;

    public function __construct(QueueRecordRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function enqueue(QueueRecord $queueRecord): bool
    {
        $queueRecord->setEntityType($this->getType());

        $this->checkRecordIsUnique($queueRecord);

        $this->repository->save($queueRecord);

        return true;
    }

    /**
     * @inheritdoc
     * В выборку попадают только записи без признака "в обработке" (обрабатываются в данный момент или зависли)
     * и без флага ошибки
     */
    public function getOneFromQueue(EntityQueryProcessingAction $action, int $strategy = self::STRATEGY_FIFO): ?QueueRecord
    {
        $order = [];

        switch ($strategy) {
            case self::STRATEGY_FIFO:
                $order['ID'] = 'ASC';
                break;
            case self::STRATEGY_LIFO:
                $order['ID'] = 'DESC';
                break;
        }

        $query = $this->repository->getQueryObject()
            ->setFilter($this->getQueryObjectFilter($action))
            ->setOrder($order)
            ->setSelect(['*']);

        return $this->repository->findOne($query);
    }

    /**
     *
     * @param EntityQueryProcessingAction $action
     * @return array
     */
    protected function getQueryObjectFilter(EntityQueryProcessingAction $action): array
    {
        return [
            '=IS_PROCESSING' => false,
            '=IS_ERROR'    => false,
            '=ENTITY_TYPE' => $this->getType(),
            '=ACTION'      => $action->getCode()
        ];
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function removeFromQueue(QueueRecord $record): bool
    {
        $this->repository->delete($record);

        return true;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function setError(QueueRecord $record, string $errorMessage)
    {
        $record->setIsProcessing(false);
        $record->setIsError(true);
        $record->setErrorText($errorMessage);

        $this->repository->save($record);
    }

    /**
     * Проверяет уникальность данных
     * Записи в очереди должны быть уникальны, не может быть двух записей одного типа с одним идентификатором
     * @param QueueRecord $queueRecord
     * @throws QueueDuplicateRecordException
     */
    private function checkRecordIsUnique(QueueRecord $queueRecord): void
    {
        $query = $this->repository->getQueryObject()
            ->setSelect(['ID'])
            ->setFilter([
                '=IS_ERROR'    => false,
                '=ENTITY_TYPE' => $queueRecord->getEntityType(),
                '=ACTION'      => $queueRecord->getAction(),
                '=ENTITY_ID'   => $queueRecord->getEntityId(),
            ]);

        $existingRecord = $this->repository->findOne($query);

        if ($existingRecord) {
            throw new QueueDuplicateRecordException('Найдена запись-дубликат ID ' . $existingRecord->getId());
        }
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function setProcessingStatus(QueueRecord $record, bool $isProcessing = true): bool
    {
        $record->setIsProcessing($isProcessing);

        return $this->repository->save($record);
    }
}
