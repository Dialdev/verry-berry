<?php

namespace Natix\Component;

use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;

/**
 * Компонент вывода фотографий магазина
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ShopImages extends CommonComponent
{
    // по сколько фотографий выводить в одном блоке
    const IMAGES_BLOCK_CHUNK = 6;
    
    /** @var array */
    protected $needModules = [
        'iblock',
        'catalog',
    ];
    
    public function __construct($component = null)
    {
        parent::__construct($component);
    }

    protected function configurate()
    {
        $this->arParams['CACHE_TYPE'] = 'Y';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        
        $this->arParams['STORE_ID'] = (int)$this->arParams['STORE_ID'];
    }
    
    protected function executeMain()
    {
        if ($this->arParams['STORE_ID'] <= 0) {
            return;
        }

        $this->arResult['IMAGES'] = $this->getImages();
    }

    /**
     * Возвращает массив фотографий магазина
     * @return array
     */
    protected function getImages(): array
    {
        $images = [];

        $iterator = \CCatalogStore::GetList(
            ['ID' => 'ASC'],
            [
                'ID' => $this->arParams['STORE_ID'],
            ],
            false,
            false,
            ['ID', 'UF_IMAGES']
        );
        
        if (($store = $iterator->GetNext()) && $store['UF_IMAGES']) {
              $ufImages = unserialize($store['UF_IMAGES']);
              
              foreach ($ufImages as $imageId) {
                  $images[] = \CFile::GetPath($imageId);
              }
        }
        
        return array_chunk($images, self::IMAGES_BLOCK_CHUNK);
    }

    /**
     * Возвращает идентификатор элемента с фотографиями магазина
     * @param int $storeId
     * @return int|null
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    protected function getElementIdByStore(int $storeId): ?int
    {
        if ($storeId <= 0) {
            return null;
        }
        
        $iterator = \CIBlockElement::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->iblockFinder->shopImages(),
                'ACTIVE' => 'Y',
                'PROPERTY_STORE_ID' => $storeId,
            ],
            false,
            ['nTopCount' => 1],
            ['ID']
        );
        
        if ($element = $iterator->Fetch()) {
            return (int)$element['ID'];
        }
        
        return null;
    }
}
