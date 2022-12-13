<?php

namespace Natix\Component;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\Component\Tools;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\HttpRequest;
use Maximaster\Tools\Twig\TemplateEngine;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Module\Api\Http\Response\ResponseInterface;
use Natix\Module\Api\Http\Response\SuccessResponse;
use Natix\Service\Catalog\Bouquets\Collection\ImageEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\PriceEntity;
use Natix\Service\Catalog\Bouquets\Service\Factory\ImageFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\PriceFactory;
use Natix\Service\Tools\Catalog\ProductTypeChecker;
use Psr\Log\LoggerInterface;

/**
 * Компонент карточки товара для простых товаров и товаров с торговыми предложениями
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogElement extends CommonComponent
{
    /** @var array */
    protected $needModules = [
        'iblock',
        'catalog',
    ];
    
    /** @var string|null */
    private $elementCode;
    
    /** @var IblockFinder */
    private $iblockFinder;
    
    /** @var LoggerInterface */
    private $logger;
    
    /** @var ImageFactory */
    private $imageFactory;
    
    /** @var PriceFactory */
    private $priceFactory;
    
    /** @var ProductTypeChecker */
    private $productTypeChecker;
    
    /** @var int */
    private $activeOfferId;
    
    public function __construct($component = null)
    {
        parent::__construct($component);
        
        $this->iblockFinder = $this->getContainer()->get(IblockFinder::class);
        $this->logger = $this->getContainer()->get(LoggerInterface::class);
        $this->imageFactory = $this->getContainer()->get(ImageFactory::class);
        $this->priceFactory = $this->getContainer()->get(PriceFactory::class);
        $this->productTypeChecker = $this->getContainer()->get(ProductTypeChecker::class);
    }

    /**
     * @return string|null
     */
    public function getElementCode(): ?string
    {
        return $this->elementCode;
    }

    /**
     * @param string|null $elementCode
     */
    public function setElementCode(?string $elementCode): void
    {
        $this->elementCode = $elementCode;
    }

    /**
     * @return int
     */
    public function getActiveOfferId(): int
    {
        return $this->activeOfferId;
    }

    /**
     * @param int $activeOfferId
     */
    public function setActiveOfferId(int $activeOfferId): void
    {
        $this->activeOfferId = $activeOfferId;
    }

    protected function configurate()
    {
        $this->arParams['CACHE_TYPE'] = 'Y';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        $this->arParams['ACTIVE_OFFER_ID'] = (int)$this->arParams['ACTIVE_OFFER_ID'];
        
        $elementCode = $this->request->get('ELEMENT_CODE') ?? $this->arParams['ELEMENT_CODE'];
        
        $this->setElementCode($elementCode);
        $this->setActiveOfferId($this->arParams['ACTIVE_OFFER_ID']);
        
        if (!$this->getElementCode()) {
            $this->process404();
        }
        
        $this->addCacheAdditionalId($this->getElementCode());
        $this->addCacheAdditionalId($this->getActiveOfferId());
    }
    
    protected function executeMain()
    {
        $this->arResult['product'] = $this->getProduct();

        $this->prepareSectionData();
    }

    /**
     * Возвращает информацию о товаре
     *
     * @return array
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException
     * @throws \Natix\Service\Tools\Catalog\Exception\ProductTypeCheckerException
     */
    protected function getProduct(): array
    {
        $iterator = \CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->iblockFinder->catalog(),
                'CODE' => $this->getElementCode(),
            ],
            false,
            ['nTopCount' => 1],
            [
                'ID',
                'NAME',
                'IBLOCK_SECTION_ID',
                'PREVIEW_PICTURE',
                'PREVIEW_TEXT',
                'DETAIL_PICTURE',
                'DETAIL_TEXT',
                'CATALOG_GROUP_1',
            ]
        );
        
        $result = [];
        
        if ($item = $iterator->Fetch()) {
            $productId = (int)$item['ID'];

            $item['PROPERTIES'] = $this->getProperties($productId, $this->iblockFinder->catalog());

            $imageIds = [];
            if ($item['PREVIEW_PICTURE']) {
                $imageIds[] = (int)$item['PREVIEW_PICTURE'];
            }
            foreach ($item['PROPERTIES']['DOP_IMAGES']['VALUES'] as $imageId) {
                if ($imageId) {
                    $imageIds[] = (int)$imageId;
                } 
            }
            $item['IMAGES'] = $this->getImages($imageIds);
            
            $priceEntity = $this->priceFactory->build(
                $this->iblockFinder->catalog(),
                $productId,
                (float)$item['CATALOG_PRICE_1']
            );
            $item['PRICE'] = PriceEntity::toState($priceEntity);
            
            if ($this->productTypeChecker->isTypeSku($productId)) {
                $item['OFFERS'] = $this->getOffers($item);
            }
            
            $result = $item;
        }
        
        return $result;
    }

    /**
     * Возвращает массив торговых предложений товара
     *
     * @param array $product
     *
     * @return array
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException
     */
    protected function getOffers(array $product): array
    {
        $productId = (int)$product['ID'];

        $iterator = \CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->iblockFinder->offers(),
                'PROPERTY_CML2_LINK' => $productId,
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PREVIEW_PICTURE',
                'CATALOG_GROUP_1',
                'SORT'
            ]
        );
        
        $offers = [];
        
        while ($offer = $iterator->Fetch()) {
            $offerId = (int)$offer['ID'];

            $offer['PROPERTIES'] = $this->getProperties($offerId, $this->iblockFinder->offers());

            $imageIds = [];
            if ($offer['PREVIEW_PICTURE']) {
                $imageIds[] = (int)$offer['PREVIEW_PICTURE'];
            }
            foreach ($offer['PROPERTIES']['DOP_IMAGES']['VALUES'] as $imageId) {
                if ($imageId) {
                    $imageIds[] = (int)$imageId;
                }
            }            
            foreach ($product['PROPERTIES']['DOP_IMAGES']['VALUES'] as $imageId) {
                if ($imageId) {
                    $imageIds[] = (int)$imageId;
                }
            }
            $offer['IMAGES'] = $this->getImages($imageIds);

            $priceEntity = $this->priceFactory->build(
                $this->iblockFinder->offers(),
                $offerId,
                (float)$offer['CATALOG_PRICE_1']
            );
            $offer['PRICE'] = PriceEntity::toState($priceEntity);
            
            $offers[$offerId] = $offer;
        }
        
        $this->prepareOffers($offers);
        
        return $offers;
    }

    /**
     * Подготавливает необходимые параметры торговых предложений
     * 
     * @param array $offers
     */
    protected function prepareOffers(array &$offers): void
    {
        if (empty($offers)) {
            return;
        }
        
        $activeOfferId = $this->getActiveOfferId() ?: (int)reset($offers)['ID'];
        $this->arResult['ACTIVE_OFFER_ID'] = $activeOfferId;
        $this->arResult['ACTIVE_OFFER'] = $offers[$activeOfferId];
        $offerPriceIsActive = 0;
        
        foreach ($offers as $key => $offer) {
            $isActive = ((int)$offer['ID'] === $activeOfferId);
            $offers[$key]['IS_ACTIVE'] = $isActive;
            if ($isActive) {
                $offerPriceIsActive = $offer['PRICE']['price_discount'];
            }
            
            $offers[$key]['JSON_PARAMS'] = [
                'ACTIVE_OFFER_ID' => (int)$offer['ID'],
                'ELEMENT_CODE' => $this->getElementCode(),
            ];
        }
        
        foreach ($offers as $key => $offer) {
            $isActive = $offer['IS_ACTIVE'];
            
            if (!$isActive) {
                $priceDiff = $offer['PRICE']['price_discount'] - $offerPriceIsActive;
                $offers[$key]['PRICE']['diff'] = $priceDiff;
                $priceDiffFormat = \CCurrencyLang::CurrencyFormat(
                    abs($priceDiff),
                    CurrencyManager::getBaseCurrency()
                );
                $offers[$key]['PRICE']['diff_format'] = sprintf(
                    '%s %s',
                    $priceDiff > 0 ? '+' : '-',
                    $priceDiffFormat
                );
            }
        }
    }

    /**
     * Возвращает свойства товара
     *
     * @param int $productId
     * @param int $iblockId
     *
     * @return array
     */
    protected function getProperties(int $productId, int $iblockId): array
    {
        $properties = [];

        $propertyIterator = \CIBlockElement::GetProperty(
            $iblockId,
            $productId,
            [
                'SORT' => 'ASC',
                'ID' => 'ASC'
            ],
            [
                'ACTIVE' => 'Y',
            ]
        );

        while ($property = $propertyIterator->Fetch()) {
            if ($property['MULTIPLE'] === 'Y') {

                $iterator = \CIBlockElement::GetProperty(
                    $iblockId,
                    $productId,
                    [
                        'SORT' => 'ASC',
                        'ID' => 'ASC'
                    ],
                    ['CODE' => $property['CODE']]
                );

                while ($item = $iterator->GetNext()) {
                    $property['VALUES'][] = $item['VALUE'];
                }
            }

            $code = trim($property['CODE']);
            $properties[$code] = $property;
        }
        
        return $properties;
    }

    /**
     * Возвращает картинки по их ID
     * 
     * @param array $imageIds
     *
     * @return array
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     */
    protected function getImages(array $imageIds): array
    {
        $images = [];
        
        if (!empty($imageIds)) {
            $imageEntityCollection = $this->imageFactory->buildByIds($imageIds, false, 131, 98);
            $images = ImageEntityCollection::toState($imageEntityCollection);
        }
        
        return $images;
    }

    /**
     * Подготавливает данные раздела
     * @throws \Exception
     */
    protected function prepareSectionData(): void
    {
        if (!$this->arResult['product']['IBLOCK_SECTION_ID']) {
            return;
        }

        $section = SectionTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '=IBLOCK_ID' => $this->iblockFinder->catalog(),
                '=ID' => $this->arResult['product']['IBLOCK_SECTION_ID'],
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!isset($section['ID']) || $section['ID'] <= 0) {
            throw new \RuntimeException(sprintf(
                'По ID "%s" не найден раздел в каталоге товаров',
                $this->arResult['product']['IBLOCK_SECTION_ID']
            ));
        }

        $this->arResult['product']['SECTION'] = $section;
    }
    
    protected function catchException(\Exception $exception, $notifier = null)
    {
        $this->logger->error(
            sprintf(
                'В компоненте %s выброшено исключение с ошибкой: %s. Backtrace: %s',
                $this->getName(),
                $exception->getMessage(),
                $exception->getTraceAsString()
            )
        );

        $this->process404();
    }

    public function returnDatas(): void
    {
        /** @var \CMain $APPLICATION */
        global $APPLICATION;

        $APPLICATION->SetTitle($this->arResult['product']['NAME']);
        
        if ($this->arResult['product']['SECTION']) {
            $APPLICATION->AddChainItem(
                $this->arResult['product']['SECTION']['NAME'],
                sprintf('/catalog/%s/', $this->arResult['product']['SECTION']['CODE'])
            );
        }

        $APPLICATION->AddChainItem($this->arResult['product']['NAME']);

        parent::returnDatas();
    }

    protected function process404(): void
    {
        Tools::process404('', true, true, true);
    }

    /**
     * Отвечает за ajax-подгрузку при выборе торгового предложения в карточке товара
     * 
     * @param HttpRequest $request
     *
     * @return ResponseInterface
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function loadProductCard(HttpRequest $request): ResponseInterface
    {
        $params = $request->toArray();

        $this->arParams = $params['PARAMS'];

        $this->configurate();

        $this->executeMain();

        $html = TemplateEngine::getInstance()->getEngine()->render(
            'natix:catalog.element:.default',
            ['result' => $this->arResult]
        );

        $response = new SuccessResponse(
            ['html' => $html],
            200
        );

        return $response;
    }
}
