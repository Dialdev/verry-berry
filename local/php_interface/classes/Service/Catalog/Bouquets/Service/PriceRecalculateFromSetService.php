<?php

namespace Natix\Service\Catalog\Bouquets\Service;

use Bitrix\Catalog\PriceTable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Web\Json;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Entity\Orm\BitrixCatalogProductSetsTable;
use Natix\Service\EntityProcessingQueue\Entity\QueueRecord;
use Psr\Log\LoggerInterface;

/**
 * Сервис пересчёта цен букетов при изменении состава комплекта
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceRecalculateFromSetService
{
    /**
     * @var PriceTypeFinder
     */
    private $priceTypeFinder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(PriceTypeFinder $priceTypeFinder, LoggerInterface $logger)
    {
        $this->priceTypeFinder = $priceTypeFinder;
        $this->logger = $logger;
    }

    public function handle(QueueRecord $queueRecord)
    {
        $setId = (int)$queueRecord->getEntityId();
        
        if ($setId <= 0) {
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
                '=SET_ID' => $setId,
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
}
