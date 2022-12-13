<?php

namespace Natix\Service\EntityProcessingQueue\Service\QueueProcessor;

use Bitrix\Main\Error;
use Natix\Helpers\EnvironmentHelper;
use Natix\Service\EntityProcessingQueue\Service\Action\EntityQueryProcessingAction;
use Natix\Service\EntityProcessingQueue\Entity\Orm\NatixEntityProcessingQueueTable;
use Natix\Service\EntityProcessingQueue\Service\QueueManager\QueueManager;
use Psr\Log\LoggerInterface;

/**
 * Базовый процессор очереди. Выполняет действие, логирует ошибки, оповещает в телеграм об ошибках.
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BaseQueueProcessor implements QueueProcessor
{
    /** @var EntityQueryProcessingAction  */
    private $action;

    /** @var QueueManager  */
    private $queueManager;
    
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        QueueManager $queueManager,
        EntityQueryProcessingAction $action,
        LoggerInterface $logger
    ) {
        $this->queueManager = $queueManager;
        $this->action = $action;
        $this->logger = $logger;
    }

    /**
     * Обрабатывает одну запись в очереди
     * Получает запись из очереди
     * Устанавливает статус "в обработке"
     * Обрабатывает
     * Удаляет из очереди
     * @inheritdoc
     */
    public function processOneFromQueue(int $strategy = QueueManager::STRATEGY_FIFO): QueueProcessingResult
    {
        $result = new QueueProcessingResult();

        if ($queueRecord = $this->queueManager->getOneFromQueue($this->action, $strategy)) {
            try {
                $result->setRecord($queueRecord);

                $this->queueManager->setProcessingStatus($queueRecord);

                $processedSuccessful = $this->action->process($queueRecord);

                if (!$processedSuccessful) {
                    throw new \Exception('При обработке произошла неизвестная ошибка. Метод-обработчик вернул false не выбросив исключение.');
                }

                $this->queueManager->removeFromQueue($queueRecord);

                $this->logger->debug(sprintf(
                    'Над сущностью ID %d из очереди успешно выполнено действие %s',
                    $queueRecord->getEntityId(),
                    $this->action->getCode()
                ), [
                    'func_name' => __METHOD__,
                ]);
            } catch (\Exception $ex) {
                $this->queueManager->setError($queueRecord, $ex->getMessage());

                $result->addError(new Error($ex->getMessage()));

                $errorMessageTemplate = <<<EOT
<b>[Natix.ru - %s] При обработке записи с ID %d из очереди произошла ошибка! [ERROR]</b>

Таблица очереди: %s

Действие: %s

Если ошибка связана с временной неработоспособностью какого-либо сервиса, удалите в БД для данной записи значения в полях IS_PROCESSING, IS_ERROR
При следующем запуске обработчика, запись будет повторно обработана.

Текст ошибки:
<pre>
%s

%s
</pre>
EOT;

                $errorMessage = sprintf(
                    $errorMessageTemplate,
                    EnvironmentHelper::getEnvironmentType(),
                    $queueRecord->getId(),
                    NatixEntityProcessingQueueTable::getTableName(),
                    $this->action->getCode(),
                    $ex->getMessage(),
                    // Размер сообщения в телеграм ограничен 4096 символами
                    substr($ex->getTraceAsString(), 0, 3500)
                );

                $this->logger->error(
                    $errorMessage,
                    [
                        'func_name' => __METHOD__,
                    ]
                );

                $this->sendTelegramErrorNotification($errorMessage);
            }
        }

        return $result;
    }

    /**
     * Отправляет оповещение в телеграм при ошибке обработки записи в очереди
     * @param $message
     */
    private function sendTelegramErrorNotification($message)
    {
        try {
            //errorNotice($message, 'Ошибка при обработке задачи в очереди');
        } catch (\Exception $e) {
            // @TODO отправить письмо с помощью bxmail() например
        }
    }
}
