<?php

namespace Natix\Service\Catalog\Bouquets\Service\QueueAction;

use Natix\Service\Catalog\Bouquets\Service\PriceRecalculateService;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;
use Natix\Service\EntityProcessingQueue\Service\Action\EntityQueryProcessingAction;

/**
 * Действие - пересчёт цен у товаров букетов-комплектов при изменении цен его составляющих
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceRecalculateAction implements EntityQueryProcessingAction
{
    /** @var PriceRecalculateService */
    private $priceRecalculateService;
    
    public function __construct(PriceRecalculateService $priceRecalculateService)
    {
        $this->priceRecalculateService = $priceRecalculateService;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return 'recalculate_bouquet_price';
    }

    /**
     * @inheritdoc
     */
    public function process(QueueRecord $queueRecord): bool
    {
        $this->priceRecalculateService->handle($queueRecord);
        
        return true;
    }
}
