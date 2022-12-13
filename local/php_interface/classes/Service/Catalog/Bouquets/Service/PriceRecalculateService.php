<?php

namespace Natix\Service\Catalog\Bouquets\Service;

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Web\Json;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Entity\Orm\BitrixCatalogProductSetsTable;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;
use Psr\Log\LoggerInterface;

/**
 * Сервис пересчёта цен для букетов комплектов
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceRecalculateService
{
    /**
     * Маппинг цен у основ букета, доп.ягод и упаковок
     *
     * @var array
     */
    private static $pricesMap;
    
    /** @var PriceTypeFinder */
    private $priceTypeFinder;
    
    /** @var IblockFinder */
    private $iblockFinder;
    
    /** @var LoggerInterface */
    private $logger;
    
    public function __construct(
        PriceTypeFinder $priceTypeFinder,
        IblockFinder $iblockFinder,
        LoggerInterface $logger
    ) {
        $this->priceTypeFinder = $priceTypeFinder;
        $this->iblockFinder = $iblockFinder;
        $this->logger = $logger;
    }

    /**
     * Пересчитывает цены у букетов комплектов. В QueueRecord прилетает запись из таблицы natix_entity_processing_queue,
     * в которой содержится идентификатор изменённой цены у основы букета, доп.ягоды или упаковки.
     * Например если изменилась цена у какой то упаковки, то нужно перечитать цены у всех букетов, содержащих в
     * комплекте эту упаковку.
     *
     * @param QueueRecord $queueRecord
     *
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function handle(QueueRecord $queueRecord)
    {
        $this->preparePricesMapping();
        $productId = (int)$queueRecord->getEntityData()['PRODUCT_ID'];
        
        if ($productId <= 0) {
            throw new \InvalidArgumentException('Не удалось получить идентификатор товара из $queueRecord');
        }
        
        $iterator = BitrixCatalogProductSetsTable::query()
            ->setSelect([
                'SET_ID',
                'OWNER_ID',
                'ITEM_ID',
            ])
            ->setFilter([
                '=ITEM_ID' => $productId,
                '=TYPE' => \CCatalogProductSet::TYPE_SET
            ])
            ->exec();

        $setsIds = [];
        
        while ($item = $iterator->fetch()) {
            $setId = (int)$item['SET_ID'];
            $ownerId = (int)$item['OWNER_ID'];
            $itemId = (int)$item['ITEM_ID'];
            if ($itemId === $ownerId) {
                continue;
            }
            $setsIds[] = $setId;
        }
        
        if (empty($setsIds)) {
            return;
        }

        $query = BitrixCatalogProductSetsTable::query()
            ->setSelect([
                'SET_ID',
                'OWNER_ID',
                'ITEM_ID',
                'QUANTITY',
                'PRICE' => 'PRICE_TABLE.PRICE'
            ])
            ->setFilter([
                '@SET_ID' => $setsIds,
            ])
            ->registerRuntimeField('PRICE_TABLE', [
                'data_type' => PriceTable::class,
                'reference' => [
                    '=this.ITEM_ID' => 'ref.PRODUCT_ID',
                    '=ref.CATALOG_GROUP_ID' => new SqlExpression('?i', $this->priceTypeFinder->base())
                ],
                'join_type' => 'left',
            ]);

        $setIterator = $query->exec();

        $setPriceMap = [];
        
        while ($setItem = $setIterator->fetch()) {
            $ownerId = (int)$setItem['OWNER_ID'];
            $itemId = (int)$setItem['ITEM_ID'];
            if ($itemId === $ownerId) {
                continue;
            }
            
            if (!isset($setPriceMap[$ownerId])) {
                $setPriceMap[$ownerId] = 0;
            }
            $price = (float)$setItem['PRICE'];
            $quantity = (int)$setItem['QUANTITY'];
            $setPriceMap[$ownerId] += $price * $quantity;
        }
        
        if (empty($setPriceMap)) {
            return;
        }
        
        $priceIdMap = [];
        $priceIterator = PriceTable::query()
            ->setSelect(['ID', 'PRODUCT_ID'])
            ->setFilter([
                '=CATALOG_GROUP_ID' => $this->priceTypeFinder->base(),
                '@PRODUCT_ID' => array_keys($setPriceMap),
            ])
            ->exec();
        while ($item = $priceIterator->fetch()) {
            $priceIdMap[$item['PRODUCT_ID']] = (int)$item['ID'];
        }

        foreach ($setPriceMap as $ownerId => $price) {
            if (isset($priceIdMap[$ownerId])) {
                $result = PriceTable::update($priceIdMap[$ownerId], [
                    'PRICE' => $price,
                ]);
                
                if (!$result->isSuccess()) {
                    $this->logger->error(
                        sprintf(
                            'Ошибка обновления цены комплекта "%s": %s',
                            $ownerId,
                            Json::encode($result->getErrorMessages())
                        ),
                        [
                            'func_name' => __METHOD__,
                            'product_id' => $ownerId,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Подготавливает маппинги цен основ букета, доп.ягод и упаковок
     *
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    private function preparePricesMapping(): void
    {
        if (self::$pricesMap === null) {
            $query = PriceTable::query()
                ->setSelect([
                    'PRODUCT_ID',
                    'PRICE',
                    'IBLOCK_ID' => 'ELEMENT.IBLOCK_ID',
                ])
                ->setFilter([
                    '=CATALOG_GROUP_ID' => $this->priceTypeFinder->base(),
                    '@ELEMENT.IBLOCK_ID' => [
                        $this->iblockFinder->bouquet(),
                        $this->iblockFinder->berries(),
                        $this->iblockFinder->packing(),
                    ],
                ]);
            
            $iterator = $query->exec();
            
            while ($item = $iterator->fetch()) {
                self::$pricesMap[$item['PRODUCT_ID']] = $item['PRICE'];
            }
        }
    }
}
