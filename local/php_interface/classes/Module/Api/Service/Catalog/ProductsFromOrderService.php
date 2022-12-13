<?php

namespace Natix\Module\Api\Service\Catalog;

use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\DB\SqlExpression;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Entity\Orm\CatalogProductTable;
use Natix\Module\Api\Service\Sale\Order\Basket\BasketService;
use Natix\Service\Catalog\Bouquets\Entity\ImageEntity;
use Natix\Service\Catalog\Bouquets\Entity\PriceEntity;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;
use Natix\Service\Catalog\Bouquets\Service\Factory\ImageFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\PriceFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\SizeFactory;

/**
 * Сервис получения списка товаров для вывода на странице оформления заказа
 *
 * @link https://redmine.book24.ru/issues/31258
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ProductsFromOrderService
{
    /** @var IblockFinder */
    private $iblockFinder;
    
    /** @var PriceTypeFinder */
    private $priceTypeFinder;
    
    /** @var BasketService */
    private $basketService;
    
    /** @var ImageFactory */
    private $imageFactory;
    
    /** @var PriceFactory */
    private $priceFactory;
    
    /** @var SizeFactory */
    private $sizeFactory;
    
    public function __construct(
        IblockFinder $iblockFinder,
        PriceTypeFinder $priceTypeFinder,
        BasketService $basketService,
        ImageFactory $imageFactory,
        PriceFactory $priceFactory,
        SizeFactory $sizeFactory
    ) {
        $this->iblockFinder = $iblockFinder;
        $this->priceTypeFinder = $priceTypeFinder;
        $this->basketService = $basketService;
        $this->imageFactory = $imageFactory;
        $this->priceFactory = $priceFactory;
        $this->sizeFactory = $sizeFactory;
    }

    public function getProducts(): array
    {
        $products = [];

        $query = CatalogProductTable::query()
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
                'PREVIEW_PICTURE',
                'IBLOCK_SECTION_ID',
                'PROPERTY_ARTICUL',
                'PROPERTY_SIZE',
                'PRODUCT_TYPE' => 'PRODUCT.TYPE',
                'PRICE' => 'PRICE_TABLE.PRICE',
            ])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=PROPERTY_SHOW_IN_ORDER' => 1,
            ])
            ->registerRuntimeField('PRODUCT', [
                'data_type' => ProductTable::class,
                'reference' => [
                    '=this.ID' => 'ref.ID'
                ],
                'join_type' => 'inner',
            ])
            ->registerRuntimeField('PRICE_TABLE', [
                'data_type' => PriceTable::class,
                'reference' => [
                    '=this.ID' => 'ref.PRODUCT_ID',
                    '=ref.CATALOG_GROUP_ID' => new SqlExpression('?i', $this->priceTypeFinder->base())
                ],
                'join_type' => 'left',
            ]);
        
        $iterator = $query->exec();
        
        $curUserBasketProductIds = $this->basketService->getCurUserBasketProductIds();

        while ($item = $iterator->fetch()) {
            // пропускаем товары, которые уже добавлены в текущую корзину пользователя
            if (in_array((int)$item['ID'], $curUserBasketProductIds, true)) {
                continue;
            }
            
            $item['URL'] = sprintf('/product/%s/', $item['CODE']);

            if ($item['PREVIEW_PICTURE']) {
                $imageEntity = $this->imageFactory->build($item['PREVIEW_PICTURE'], true, 131,98);
                $item['IMAGE'] = ImageEntity::toState($imageEntity);
            }
            unset($item['PREVIEW_PICTURE']);

            $priceEntity = $this->priceFactory->build($this->iblockFinder->catalog(), $item['ID'], $item['PRICE']);
            $item['PRICES'] = PriceEntity::toState($priceEntity);
            unset($item['PRICE']);

            if ($item['PROPERTY_SIZE'] > 0) {
                $sizeEntity = $this->sizeFactory->buildById($item['PROPERTY_SIZE']);
                $item['SIZE'] = SizeEntity::toState($sizeEntity);
            }
            unset($item['PROPERTY_SIZE']);

            $products[$item['ID']] = $item;
        }
        
        return $products;
    }
}
