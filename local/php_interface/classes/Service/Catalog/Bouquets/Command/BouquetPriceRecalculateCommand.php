<?php

namespace Natix\Service\Catalog\Bouquets\Command;

use Bitrix\Main\Loader;
use Natix\Infrastructure\ConsoleJedi\BaseConsoleCommand;
use Natix\Service\Catalog\Bouquets\Service\QueueAction\PriceRecalculateAction;
use Natix\Service\Catalog\Bouquets\Service\QueueAction\PriceRecalculateFromSetAction;
use Natix\Service\EntityProcessingQueue\Service\QueueManager\PriceQueueManager;
use Natix\Service\EntityProcessingQueue\Service\QueueManager\SetQueueManager;
use Natix\Service\EntityProcessingQueue\Service\QueueProcessor\BaseQueueProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда для пересчёта цен товара букета-комплекта
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BouquetPriceRecalculateCommand extends BaseConsoleCommand
{
    use LockableTrait;

    /** @var PriceQueueManager */
    private $priceQueueManager;
    
    /** @var SetQueueManager */
    private $setQueueManager;
    
    /** @var PriceRecalculateAction */
    private $priceRecalculateAction;
    
    /** @var PriceRecalculateFromSetAction */
    private $priceRecalculateFromSetAction;
    
    /** @var LoggerInterface */
    private $logger;

    protected function configure()
    {
        $this->setName('app:bouquet-price:recalculate')
            ->setDescription('Пересчитывает и обновляет цену букетов-комплектов');
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);        
        Loader::includeModule('iblock');
        Loader::includeModule('catalog');

        $this->priceQueueManager = \Natix::$container->get(PriceQueueManager::class);
        $this->setQueueManager = \Natix::$container->get(SetQueueManager::class);
        $this->priceRecalculateAction = \Natix::$container->get(PriceRecalculateAction::class);
        $this->priceRecalculateFromSetAction = \Natix::$container->get(PriceRecalculateFromSetAction::class);
        $this->logger = \Natix::$container->get(LoggerInterface::class);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loggerContext = [
            'func_name' => __METHOD__,
            'command_name' => $this->getName(),
        ];

        if (!$this->lock()) {
            $this->logger->warning(
                sprintf('Команда %s уже запущена', $this->getName()),
                $loggerContext
            );

            return 0;
        }

        $this->logger->notice(
            sprintf('Команда %s запущена', $this->getName()),
            $loggerContext
        );

        try {
            $processor = new BaseQueueProcessor(
                $this->priceQueueManager,
                $this->priceRecalculateAction,
                $this->logger
            );

            while (($result = $processor->processOneFromQueue())->getRecord() !== null) {
                if ($result->isSuccess()) {
                    $this->lln(sprintf('Запись %d успешно обработана', $result->getRecord()->getId()));
                } else {
                    $this->lln(
                        sprintf(
                            'При обработке записи %d произошла ошибка: %s',
                            $result->getRecord()->getId(),
                            implode(', ', $result->getErrorMessages())
                        )
                    );
                }
            }
            
            $processor = new BaseQueueProcessor(
                $this->setQueueManager,
                $this->priceRecalculateFromSetAction,
                $this->logger
            );
            
            while (($result = $processor->processOneFromQueue())->getRecord() !== null) {
                if ($result->isSuccess()) {
                    $this->lln(sprintf('Запись %d успешно обработана', $result->getRecord()->getId()));
                } else {
                    $this->lln(
                        sprintf(
                            'При обработке записи %d произошла ошибка: %s',
                            $result->getRecord()->getId(),
                            implode(', ', $result->getErrorMessages())
                        )
                    );
                }
            }
            
        } catch (\Throwable $throwable) {
            $errorNotice = sprintf(
                'В команде %s произошла ошибка: %s. Trace %s',
                $this->getName(),
                $throwable->getMessage(),
                $throwable->getTraceAsString()
            );

            $this->logger->error($errorNotice, $loggerContext);

            $this->lln($errorNotice);

            throw $throwable;
        } finally {
            $this->endExecuteCommand();
            $this->release();
        }

        return 0;
    }
}
