<?php

namespace Natix\Component;

use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Context;
use Bitrix\Main\DB\SqlExpression;
use Natix\Data\Bitrix\Finder\Catalog\PriceTypeFinder;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Entity\Orm\CatalogProductTable;
use Natix\Entity\Orm\Iblock\ElementTable;
use Natix\Entity\Orm\OffersTable;
use Natix\Service\Catalog\Bouquets\Entity\ImageEntity;
use Natix\Service\Catalog\Bouquets\Entity\PriceEntity;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;
use Natix\Service\Catalog\Bouquets\Service\Factory\ImageFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\PriceFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\SizeFactory;
use Natix\UI\PageNavigation;
use Psr\Log\LoggerInterface;

/**
 * Компонент списка простых товаров и товаров с торговыми предложениями
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogList extends CommonComponent
{
    /** @var array */
    protected $needModules = [
        'iblock',
        'catalog',
    ];

    /** @var array */
    private $section;

    /** @var PageNavigation|null */
    private $pagination;

    /** @var array */
    private $filter;

    /** @var LoggerInterface */
    private $logger;

    /** @var IblockFinder */
    private $iblockFinder;

    /** @var PriceTypeFinder */
    private $priceTypeFinder;
    
    /** @var ImageFactory */
    private $imageFactory;
    
    /** @var PriceFactory */
    private $priceFactory;
    
    /** @var SizeFactory */
    private $sizeFactory;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->logger = $this->getContainer()->get(LoggerInterface::class);
        $this->iblockFinder = $this->getContainer()->get(IblockFinder::class);
        $this->priceTypeFinder = $this->getContainer()->get(PriceTypeFinder::class);
        $this->imageFactory = $this->getContainer()->get(ImageFactory::class);
        $this->priceFactory = $this->getContainer()->get(PriceFactory::class);
        $this->sizeFactory = $this->getContainer()->get(SizeFactory::class);
    }

    protected function configurate()
    {
        $this->arParams['CACHE_TYPE'] = 'N';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        $this->arParams['SECTION_CODE'] = $this->request->get('SECTION_CODE');

        $this->filter = $this->arParams['FILTER'] ?? [];

        $this->arParams['ELEMENT_PER_PAGE'] = (int) $this->arParams['ELEMENT_PER_PAGE'] ?: 6;
        $this->arParams['SORT_FIELD'] = $this->arParams['SORT_FIELD'] ?? 'SORT';
        $this->arParams['SORT_ORDER'] = $this->arParams['SORT_ORDER'] ?? 'ASC';

        $this->addCacheAdditionalId(Context::getCurrent()->getRequest()->getRequestUri());
    }
    
    protected function executeMain()
    {
        $this->initPagination();
        
        $this->prepareSectionData();
        
        $this->arResult['SECTION'] = $this->section;
        
        $this->arResult['LIST'] = $this->getProductsList();
        
        $this->postProcessingProductList();

        $this->arResult['NAV'] = $this->getNavParams();
    }

    /**
     * Init page navigation
     */
    public function initPagination()
    {
        $this->pagination = new PageNavigation('page');

        $this->pagination->allowAllRecords(false)
            ->setPageSize($this->arParams['ELEMENT_PER_PAGE'])
            ->initFromUri();
    }

    protected function getNavParams(): array
    {
        return [
            'id' => $this->pagination->getId(),
            'pageSizes' => $this->pagination->getPageSizes(),
            'pageSize' => $this->pagination->getPageSize(),
            'pageCount' => $this->pagination->getPageSize() > 0
                ? ceil($this->pagination->getRecordCount() / $this->pagination->getPageSize())
                : 0,
            'recordCount' => $this->pagination->getRecordCount(),
            'currentPage' => $this->pagination->getCurrentPage(),
            'allowAll' => $this->pagination->allRecordsAllowed(),
            'allRecords' => $this->pagination->allRecordsShown(),
        ];
    }

    public function returnDatas(): void
    {
        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        $APPLICATION->SetTitle($this->section['NAME']);

        $APPLICATION->AddChainItem($this->section['NAME']);

       // Подключение SEO из раздела инфоблока
       $iblock_id = $this->iblockFinder->catalog();
       $section_id = $this->section['ID'];
       
       $iblockSectionSeoValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($iblock_id, $section_id);
       $META_SECTION  = $iblockSectionSeoValues->getValues();

       $APPLICATION->SetPageProperty("title", $META_SECTION['SECTION_META_TITLE']);
       $APPLICATION->SetPageProperty("description", $META_SECTION['SECTION_META_DESCRIPTION']);
       $APPLICATION->SetPageProperty("keywords", $META_SECTION['SECTION_META_KEYWORDS']);

        parent::returnDatas();
    }

    /**
     * Подготавливает данные раздела
     * @throws \Exception
     */
    protected function prepareSectionData(): void
    {
        if (!$this->arParams['SECTION_CODE']) {
            return;
        }

        $section = SectionTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=CODE' => $this->arParams['SECTION_CODE'],
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!isset($section['ID']) || $section['ID'] <= 0) {
            throw new \RuntimeException(sprintf(
                'По коду "%s" не найден раздел в каталоге товаров',
                $this->arParams['SECTION_CODE']
            ));
        }
        
        $this->section = $section;
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException
     */
    protected function getProductsList(): array
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/helpers/SectionsHelper.php';
        
        $list = [];
        
        $filter = $this->getFilterDefault();
        if (!empty($this->filter)) {
            $filter = array_merge($filter, $this->filter);
        }
        
        if(\helpers\SectionsHelper::isSectionInRightLocation($_SERVER['REQUEST_URI']) === false)
            return [];

        $filter = $this->setFilterByLocations($filter);
        
        $query = CatalogProductTable::query()
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
                'PREVIEW_PICTURE',
                'IBLOCK_SECTION_ID',
                'PROPERTY_ARTICUL',
                'PROPERTY_SIZE',
                'PROPERTY_CITY',
                'PRODUCT_TYPE' => 'PRODUCT.TYPE',
                'PRICE' => 'PRICE_TABLE.PRICE',
            ])
            ->setFilter($filter)
            ->setOrder([
                $this->arParams['SORT_FIELD'] => $this->arParams['SORT_ORDER'],
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

        if ($this->pagination !== null) {
            $query->setLimit($this->pagination->getLimit());
            if ($this->pagination->getOffset() > 0) {
                $query->setOffset($this->pagination->getOffset());
            }
            $query->countTotal(true);
        }
        
        $iterator = $query->exec();
        
        $this->pagination->setRecordCount($iterator->getCount());
        
        while ($item = $iterator->fetch()) {
            $item['URL'] = sprintf('/product/%s/', $item['CODE']);
            
            if ($item['PREVIEW_PICTURE']) {
                $imageEntity = $this->imageFactory->build($item['PREVIEW_PICTURE'], true, 131,98);
                $item['IMAGE'] = ImageEntity::toState($imageEntity);
            }
            
            $priceEntity = $this->priceFactory->build($this->iblockFinder->catalog(), $item['ID'], $item['PRICE']);
            $item['PRICES'] = PriceEntity::toState($priceEntity);
            
            if ($item['PROPERTY_SIZE'] > 0) {
                $sizeEntity = $this->sizeFactory->buildById($item['PROPERTY_SIZE']);
                $item['SIZE'] = SizeEntity::toState($sizeEntity);
            }
            
            $list[$item['ID']] = $item;
        }
        
        return $list;
    }

    /**
     * Отфильтровать товары по городу пользователя
     *
     * @param array $filter
     * @return array
     */
    protected function setFilterByLocations(array $filter): array
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/helpers/SectionsHelper.php';
        
        $location = \helpers\SectionsHelper::getUserLocation();

        if (!$location)
            return $filter;

        $filterProperty = null;

        $properties = \CIBlockProperty::GetPropertyEnum('CITY');

        while ($property = $properties->GetNext()) {
            if ($property['XML_ID'] == $location) {
                $filterProperty = $property['ID'];
                break;
            }
        }

        if ($filterProperty) {
            $filter[] = [
                'LOGIC'          => 'OR',
                '=PROPERTY_CITY' => $filterProperty,
                'PROPERTY_CITY'  => null,
            ];
        }

        return $filter;
    }
    
    protected function postProcessingProductList()
    {
        $productIds = [];
        
        foreach ($this->arResult['LIST'] as $productId => $item) {
            if ($productId > 0) {
                $productIds[] = $productId;
            }
        }
        
        $offers = [];
        
        if (!empty($productIds)) {
            $skuIterator = OffersTable::query()
                ->setSelect([
                    'ID',
                    'PREVIEW_PICTURE',
                    'PROPERTY_CML2_LINK',
                    'PRICE' => 'PRICE_TABLE.PRICE',
                ])
                ->setFilter([
                    '=IBLOCK_ID' => $this->iblockFinder->offers(),
                    '@PROPERTY_CML2_LINK' => $productIds,
                ])
                ->setOrder(['SORT' => 'ASC'])
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
                ])
                ->exec();
                    
            while ($sku = $skuIterator->fetch()) {
                if (isset($offers[$sku['PROPERTY_CML2_LINK']])) {
                    continue;
                }

                if ($sku['PREVIEW_PICTURE']) {
                    $imageEntity = $this->imageFactory->build($sku['PREVIEW_PICTURE'], true, 131,98);
                    $sku['IMAGE'] = ImageEntity::toState($imageEntity);
                }

                $priceEntity = $this->priceFactory->build($this->iblockFinder->offers(), $sku['ID'], $sku['PRICE']);
                $sku['PRICES'] = PriceEntity::toState($priceEntity);

                $sku['URL_ADD'] = sprintf('?offer_id=%s', $sku['ID']);
                
                $offers[$sku['PROPERTY_CML2_LINK']] = $sku;
            }
        }

        foreach ($this->arResult['LIST'] as $productId => $item) {
            if (!isset($offers[$productId])) {
                continue;
            }

            $this->arResult['LIST'][$productId]['IMAGE'] = $offers[$productId]['IMAGE'];
            $this->arResult['LIST'][$productId]['PRICES'] = $offers[$productId]['PRICES'];
            $this->arResult['LIST'][$productId]['URL'] = $this->arResult['LIST'][$productId]['URL'] . $offers[$productId]['URL_ADD'];
        }
    }

    /**
     * Возвращает дефолтное значение фильтра товаров
     * @return array
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    protected function getFilterDefault(): array
    {
        $defaultFilter['=IBLOCK_ID'] = $this->iblockFinder->catalog();
        $defaultFilter['PROPERTY_CATALOG_LIST'] = 1;
        
        if ($this->section['ID'] !== null) {
            //$defaultFilter['=IBLOCK_SECTION_ID'] = $this->section['ID'];
            $defaultFilter['>=IBLOCK_SECTION.LEFT_MARGIN'] = $this->section['LEFT_MARGIN'];
            $defaultFilter['<=IBLOCK_SECTION.RIGHT_MARGIN'] = $this->section['RIGHT_MARGIN'];
        }
        return $defaultFilter;
    }
}
