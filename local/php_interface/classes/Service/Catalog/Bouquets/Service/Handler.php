<?php

namespace Natix\Service\Catalog\Bouquets\Service;

use Bitrix\Main\Event;
use Natix\Service\EntityProcessingQueue\Factory\QueueRecordFactory;
use Natix\Service\EntityProcessingQueue\Service\QueueManager\PriceQueueManager;
use Natix\Service\EntityProcessingQueue\Service\QueueManager\SetQueueManager;
use Psr\Log\LoggerInterface;

/**
 * Обработчик событий сервиса букетов
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Handler
{
    /** @var QueueRecordFactory */
    private $queueRecordFactory;
    
    /** @var PriceQueueManager */
    private $priceQueueManager;

    /** @var SetQueueManager */
    private $setQueueManager;
    
    /** @var QueueAction\PriceRecalculateAction */
    private $priceRecalculateAction;
    
    /** @var QueueAction\PriceRecalculateFromSetAction */
    private $priceRecalculateFromSetAction;
    
    /** @var LoggerInterface */
    private $logger;
    
    public function __construct(
        QueueRecordFactory $queueRecordFactory,
        PriceQueueManager $priceQueueManager,
        SetQueueManager $setQueueManager,
        QueueAction\PriceRecalculateAction $priceRecalculateAction,
        QueueAction\PriceRecalculateFromSetAction $priceRecalculateFromSetAction,
        LoggerInterface $logger
    ) {
        $this->queueRecordFactory = $queueRecordFactory;
        $this->priceQueueManager = $priceQueueManager;
        $this->setQueueManager = $setQueueManager;
        $this->priceRecalculateAction = $priceRecalculateAction;
        $this->priceRecalculateFromSetAction = $priceRecalculateFromSetAction;
        $this->logger = $logger;
    }

    /**
     * Добавляет ID товара из инфоблокав "Основа букета", "Ягоды" или "Упаковка"
     * в таблицу очередей natix_entity_processing_queue.
     * 
     * На основе записи из этой таблицы у товара букета-комплекта будет пересчитана цена
     * 
     * @param Event $event
     */
    public function bouquetPriceRecalculate(Event $event)
    {
        $priceId = (int)$event->getParameter('id')['ID'];
        $fields = $event->getParameter('fields');
        
        if ($priceId > 0 && is_array($fields)) {
            try {
                $record = $this->queueRecordFactory->buildForEnqueue($priceId, $this->priceRecalculateAction, $fields);
                $this->priceQueueManager->enqueue($record);
            } catch (\Exception $exception) {
                $this->logger->error(
                    sprintf(
                        'Ошибка добавления цены товара %s в очередь: %s',
                        $fields['PRODUCT_ID'],
                        $exception->getMessage()
                    ),
                    ['func_name' => __METHOD__]
                );
            }
        }
    }
    
    public function bouquetPriceRecalculateBySetAdd($id, $arFields): void
    {
        if ($id > 0) {
            try {
                $record = $this->queueRecordFactory->buildForEnqueue($id, $this->priceRecalculateFromSetAction, $arFields);
                $this->setQueueManager->enqueue($record);
            } catch (\Exception $exception) {
                $this->logger->error(
                    sprintf(
                        'Ошибка добавления комплекта %s в очередь: %s',
                        $id,
                        $exception->getMessage()
                    ),
                    ['func_name' => __METHOD__]
                );
            }
        }
    }

    public function bouquetPriceRecalculateBySetUpdate($id, $arFields): void
    {
        if ($id > 0) {
            try {
                $record = $this->queueRecordFactory->buildForEnqueue($id, $this->priceRecalculateFromSetAction, $arFields);
                $this->setQueueManager->enqueue($record);
            } catch (\Exception $exception) {
                $this->logger->error(
                    sprintf(
                        'Ошибка добавления комплекта %s в очередь: %s',
                        $id,
                        $exception->getMessage()
                    ),
                    ['func_name' => __METHOD__]
                );
            }
        }
    }
    
    public function bouquetPriceRecalculateBySetDelete($id): void
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/zzz.txt', print_r($id, 1));
        if ($id > 0) {
            try {
                $record = $this->queueRecordFactory->buildForEnqueue($id, $this->priceRecalculateFromSetAction);
                $this->setQueueManager->enqueue($record);
            } catch (\Exception $exception) {
                $this->logger->error(
                    sprintf(
                        'Ошибка добавления комплекта %s в очередь: %s',
                        $id,
                        $exception->getMessage()
                    ),
                    ['func_name' => __METHOD__]
                );
            }
        }
    }
}
