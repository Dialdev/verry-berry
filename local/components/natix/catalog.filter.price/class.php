<?php

namespace Natix\Component;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Web\Uri;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;

/**
 * Компонент фильтра по цене в списке товаров
 * Диапазоны фильтров по цене берутся из пользовательского свойства раздела "Диапазоны фильтра по цене"
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogFilterPrice extends CommonComponent
{
    const PRICE_FROM_PARAM = 'price_from';
    const PRICE_TO_PARAM = 'price_to';
    
    protected $needModules = [
        'iblock',
    ];
    
    /** @var IblockFinder */
    private $iblockFinder;

    /** @var array */
    private $section;
    
    /** @var float|null */
    private $selectedPriceFrom;
    
    /** @var float|null */
    private $selectedPriceTo;
    
    public function __construct($component = null)
    {
        parent::__construct($component);
        
        $this->iblockFinder = $this->getContainer()->get(IblockFinder::class);
    }

    /**
     * @return float
     */
    public function getSelectedPriceFrom(): ?float
    {
        return $this->selectedPriceFrom;
    }

    /**
     * @return float
     */
    public function getSelectedPriceTo(): ?float
    {
        return $this->selectedPriceTo;
    }

    /**
     * @param float $selectedPriceFrom
     * @return CatalogFilterPrice
     */
    public function setSelectedPriceFrom(float $selectedPriceFrom): CatalogFilterPrice
    {
        $this->selectedPriceFrom = $selectedPriceFrom;
        return $this;
    }

    /**
     * @param float $selectedPriceTo
     * @return CatalogFilterPrice
     */
    public function setSelectedPriceTo(float $selectedPriceTo): CatalogFilterPrice
    {
        $this->selectedPriceTo = $selectedPriceTo;
        return $this;
    }

    protected function configurate()
    {
        $this->arParams['CACHE_TYPE'] = 'N';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        $this->arParams['SECTION_CODE'] = $this->arParams['SECTION_CODE'] ?? $this->request->get('SECTION_CODE');
        
        global $arrFilter;
        if ($this->request->get(self::PRICE_FROM_PARAM) !== null) {
            $this->setSelectedPriceFrom((float)$this->request->get(self::PRICE_FROM_PARAM));
            $arrFilter['>=PRICE'] = $this->getSelectedPriceFrom();
        }
        if ($this->request->get(self::PRICE_TO_PARAM) !== null) {
            $this->setSelectedPriceTo((float)$this->request->get(self::PRICE_TO_PARAM));
            $arrFilter['<=PRICE'] = $this->getSelectedPriceTo();
        }

        $this->addCacheAdditionalId($this->getSelectedPriceFrom());
        $this->addCacheAdditionalId($this->getSelectedPriceTo());
    }
    
    protected function executeMain()
    {
        $this->prepareSectionData();
        $this->arResult['section'] = $this->section;
        $this->arResult['filter_prices'] = $this->getFilterPrices();
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
        
        $section = \CIBlockSection::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->iblockFinder->catalog(),
                'CODE' => $this->arParams['SECTION_CODE'],
            ],
            false,
            ['ID', 'IBLOCK_ID', 'NAME', 'UF_PRICE_FILTER'],
            ['nTopCount' => 1]
        )->Fetch();

        if (!isset($section['ID']) || $section['ID'] <= 0) {
            throw new \RuntimeException(sprintf(
                'По коду "%s" не найден раздел в каталоге товаров',
                $this->arParams['SECTION_CODE']
            ));
        }

        $this->section = $section;
    }
    
    protected function getFilterPrices(): array
    {
        $result = [];

        $requestUri = $this->request->getRequestUri();
        if (strpos($requestUri, $this->arParams['SECTION_CODE']) === false) {
            $requestUri = sprintf('/catalog/%s%s', $this->arParams['SECTION_CODE'], $requestUri);
        }
        
        foreach ($this->section['UF_PRICE_FILTER'] as $item) {
            
            $priceFilter = unserialize($item);
            
            $priceFrom = (float)$priceFilter['PRICE_FROM'];
            $priceTo = (float)$priceFilter['PRICE_TO'];

            $uri = new Uri($requestUri);
            
            $isSelected = false;

            if (!$priceFrom && $priceTo) {
                $filterName = sprintf(
                    'До %s',
                    \CCurrencyLang::CurrencyFormat($priceTo, CurrencyManager::getBaseCurrency())
                );
                $uri->deleteParams([self::PRICE_FROM_PARAM])
                    ->addParams([self::PRICE_TO_PARAM => $priceTo]);
                
                if (!$this->getSelectedPriceFrom() && $this->getSelectedPriceTo()) {
                    $isSelected = true;
                }
                
            } elseif ($priceFrom && $priceTo) {
                $filterName = sprintf(
                    '%s - %s',
                    \CCurrencyLang::CurrencyFormat($priceFrom, CurrencyManager::getBaseCurrency()),
                    \CCurrencyLang::CurrencyFormat($priceTo, CurrencyManager::getBaseCurrency())
                );
                $uri->addParams([
                    self::PRICE_FROM_PARAM => $priceFrom,
                    self::PRICE_TO_PARAM => $priceTo,
                ]);
                
                if ($this->getSelectedPriceFrom() && $this->getSelectedPriceTo()) {
                    $isSelected = true;
                }
            } elseif ($priceFrom && !$priceTo) {
                $filterName = sprintf(
                    'От %s',
                    \CCurrencyLang::CurrencyFormat($priceFrom, CurrencyManager::getBaseCurrency())
                );
                $uri->deleteParams([self::PRICE_TO_PARAM])
                    ->addParams([self::PRICE_FROM_PARAM => $priceFrom]);

                if ($this->getSelectedPriceFrom() && !$this->getSelectedPriceTo()) {
                    $isSelected = true;
                }
            } else {
                continue;
            }
            
            $result[] = [
                'name' => $filterName,
                'url' => $uri->getUri(),
                'isSelected' => $isSelected,
            ];
        }

        return $result;
    }
}
