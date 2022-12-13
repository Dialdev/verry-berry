<?php

namespace Natix\Service\Catalog\Bouquets\Service\QueueAction;

use Natix\Service\Catalog\Bouquets\Service\PriceRecalculateFromSetService;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;
use Natix\Service\EntityProcessingQueue\Service\Action\EntityQueryProcessingAction;

/**
 * Пересчёт цен у букетов при изменении состава комплекта
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceRecalculateFromSetAction implements EntityQueryProcessingAction
{
    /**
     * @var PriceRecalculateFromSetService
     */
    private $priceRecalculateFromSetService;

    /**
     * PriceRecalculateFromSetAction constructor.
     *
     * @param PriceRecalculateFromSetService $priceRecalculateFromSetService
     */
    public function __construct(PriceRecalculateFromSetService $priceRecalculateFromSetService)
    {
        $this->priceRecalculateFromSetService = $priceRecalculateFromSetService;
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'recalculate_bouquet_price_from_set';
    }

    /**
     * @inheritDoc
     */
    public function process(QueueRecord $queueRecord): bool
    {
        $this->priceRecalculateFromSetService->handle($queueRecord);
        
        return true;
    }
}
