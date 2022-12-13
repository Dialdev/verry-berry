<?php

namespace Natix\Service\EntityProcessingQueue\Entity;

use Bitrix\Main\Type\DateTime;

/**
 * Одна запись в очереди
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class QueueRecord
{
    /** @var int */
    private $id;

    /** @var DateTime Время постановки в очередь */
    private $enqueueTime;

    /** @var string */
    private $entityType;

    /** @var string */
    private $entityId;

    /** @var bool  */
    private $isProcessing;

    /** @var string */
    private $action;

    /** @var array  */
    private $entityData;

    /** @var bool  */
    private $isError;

    /** @var string  */
    private $errorText;

    public static function fromState(array $state): QueueRecord
    {
        $record = new static();

        if (isset($state['ID'])) {
            $record->setId($state['ID']);
        }

        if (isset($state['ENQUEUE_DATE'])) {
            $record->setEnqueueTime($state['ENQUEUE_DATE']);
        }

        if (isset($state['ENTITY_TYPE'])) {
            $record->setEntityType($state['ENTITY_TYPE']);
        }

        if (isset($state['ENTITY_ID'])) {
            $record->setEntityId($state['ENTITY_ID']);
        }

        if (isset($state['IS_PROCESSING'])) {
            $record->setIsProcessing($state['IS_PROCESSING']);
        }

        if (isset($state['ACTION'])) {
            $record->setAction($state['ACTION']);
        }

        if (isset($state['ENTITY_DATA'])) {
            $record->setEntityData($state['ENTITY_DATA']);
        }

        if (isset($state['IS_ERROR'])) {
            $record->setIsError($state['IS_ERROR']);
        }

        if (isset($state['ERROR_TEXT'])) {
            $record->setErrorText($state['ERROR_TEXT']);
        }

        return $record;
    }

    public static function toState(QueueRecord $entity): array
    {
        return [
            'ID' => $entity->getId(),
            'ENQUEUE_DATE' => $entity->getEnqueueTime(),
            'ENTITY_TYPE' => $entity->getEntityType(),
            'ENTITY_ID' => $entity->getEntityId(),
            'IS_PROCESSING' => $entity->isProcessing(),
            'ACTION' => $entity->getAction(),
            'ENTITY_DATA' => $entity->getEntityData(),
            'IS_ERROR' => $entity->isError(),
            'ERROR_TEXT' => $entity->getErrorText()
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return DateTime
     */
    public function getEnqueueTime(): DateTime
    {
        return $this->enqueueTime;
    }

    /**
     * @param DateTime $enqueueTime
     */
    public function setEnqueueTime(DateTime $enqueueTime)
    {
        $this->enqueueTime = $enqueueTime;
    }

    /**
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @param string $entityType
     */
    public function setEntityType(string $entityType)
    {
        $this->entityType = $entityType;
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId(string $entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->isProcessing;
    }

    /**
     * @param bool $isProcessing
     */
    public function setIsProcessing(bool $isProcessing)
    {
        $this->isProcessing = $isProcessing;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action)
    {
        $this->action = $action;
    }

    /**
     * @return array
     */
    public function getEntityData(): array
    {
        return $this->entityData;
    }

    /**
     * @param array $entityData
     */
    public function setEntityData(array $entityData)
    {
        $this->entityData = $entityData;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->isError;
    }

    /**
     * @param bool $isError
     */
    public function setIsError(bool $isError)
    {
        $this->isError = $isError;
    }

    /**
     * @return string
     */
    public function getErrorText(): string
    {
        return $this->errorText;
    }

    /**
     * @param string $errorText
     */
    public function setErrorText(string $errorText)
    {
        $this->errorText = $errorText;
    }
}
