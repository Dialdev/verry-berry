<?php

/**
 * Репозиторий записей в очереди
 * @author Pavel Ivanov <itmariacchi@gmail.com>
 * Date: 24.04.2018
 */

namespace Natix\Service\EntityProcessingQueue\Repository;

use Bitrix\Main\Entity\Query;
use Bitrix\Main\Type\DateTime;
use Natix\Service\EntityProcessingQueue\Entity\Orm\NatixEntityProcessingQueueTable;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;
use Natix\Service\EntityProcessingQueue\Exception\QueueRecordRepositoryDeleteException;
use Natix\Service\EntityProcessingQueue\Exception\QueueRecordRepositorySaveException;

/**
 * Репозиторий записей в очереди
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class QueueRecordRepository
{
    /**
     * Возвращает объект запроса
     * @return Query
     */
    public function getQueryObject()
    {
        return NatixEntityProcessingQueueTable::query();
    }

    /**
     * @param QueueRecord $queueRecord
     *
     * @return bool
     * @throws \Exception
     * @throws QueueRecordRepositorySaveException
     */
    public function save(QueueRecord $queueRecord): bool
    {
        $queueRecord->setEnqueueTime(new DateTime());

        $recordData = QueueRecord::toState($queueRecord);

        if ($queueRecord->getId() === 0) {
            $dbResult = NatixEntityProcessingQueueTable::add($recordData);
        } else {
            $dbResult = NatixEntityProcessingQueueTable::update($queueRecord->getId(), $recordData);
        }

        if (!$dbResult->isSuccess()) {
            throw new QueueRecordRepositorySaveException(implode('. ', $dbResult->getErrorMessages()));
        }

        return true;
    }

    /**
     * @param QueueRecord $queueRecord
     * @throws QueueRecordRepositoryDeleteException
     */
    public function delete(QueueRecord $queueRecord): void
    {
        $deleteResult = NatixEntityProcessingQueueTable::delete($queueRecord->getId());

        if (!$deleteResult->isSuccess()) {
            throw new QueueRecordRepositoryDeleteException(
                sprintf(
                    'Ошибка удаления записи %d из очереди: %s',
                    $queueRecord->getId(),
                    implode(', ', $deleteResult->getErrorMessages())
                )
            );
        }
    }

    /**
     * @param Query $query
     * @return QueueRecord[]
     */
    public function find(Query $query): array
    {
        $result = $query->exec();

        $items = [];

        while ($item = $result->fetch()) {
            $items[] = QueueRecord::fromState($item);
        }

        return $items;
    }

    /**
     * @param Query $query
     * @return QueueRecord|null
     */
    public function findOne(Query $query)
    {
        $query->setLimit(1);

        $result = $this->find($query);

        return !empty($result) ? $result[0] : null;
    }

    /**
     * Возвращает запись по типу, действию и её ID
     *
     * @param string $entityType
     * @param string $action
     * @param string $entityId
     *
     * @return null|QueueRecord
     */
    public function findOneByTypeAndActionAndId($entityType, $action, $entityId): ?QueueRecord
    {
        $query = $this->getQueryObject()
            ->setSelect(['*'])
            ->setFilter([
                '=ENTITY_TYPE' => $entityType,
                '=ACTION' => $action,
                '=ENTITY_ID' => $entityId,
            ]);

        return $this->findOne($query);
    }
}
