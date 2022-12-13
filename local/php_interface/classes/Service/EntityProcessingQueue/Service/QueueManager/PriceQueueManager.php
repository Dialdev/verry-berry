<?php

namespace Natix\Service\EntityProcessingQueue\Service\QueueManager;

use Bitrix\Iblock\ElementTable;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;

/**
 * Менеджер очереди для цен товаров
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceQueueManager extends BaseQueueManager
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'price';
    }
    
    public function enqueue(QueueRecord $queueRecord): bool
    {
        $priceId = (int)$queueRecord->getEntityId();
        $productId = (int)$queueRecord->getEntityData()['PRODUCT_ID'];
        
        if (!$priceId) {
            throw new \RuntimeException('не передан идентификатор цены');
        }
        
        if (!$productId) {
            throw new \RuntimeException('Не передан идентификатор товара');
        }

        if (!$this->isBouquetAccessoriesPrice($productId)) {
            throw new \RuntimeException(
                sprintf(
                    'Цена %d товара %d не относится к составу букета',
                    $priceId,
                    $queueRecord->getEntityData()['PRODUCT_ID']
                )
            );
        }
        
        return parent::enqueue($queueRecord);
    }

    /**
     * Проверяет, что цена относится к цене комплектующего букета
     * (т.е. основа букета, ягоды или упаковка)
     *
     * @param int $productId
     *
     * @return bool
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    private function isBouquetAccessoriesPrice(int $productId): bool
    {
        /** @var IblockFinder $iblockFinder */
        $iblockFinder = \Natix::$container->get(IblockFinder::class);
        
        $element = ElementTable::getRow([
            'select' => ['IBLOCK_ID'],
            'filter' => [
                '=ID' => $productId,
            ]
        ]);
        
        if (!isset($element['IBLOCK_ID'])) {
            return false;
        }
        
        return in_array((int)$element['IBLOCK_ID'], [
            $iblockFinder->bouquet(),
            $iblockFinder->berries(),
            $iblockFinder->packing()
        ], true);
    }
}
