<?php

namespace Natix\Component;

use Bitrix\Iblock\ElementTable;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Data\Bitrix\Finder\Iblock\IblockPropertyFinder;
use Psr\Log\LoggerInterface;

/**
 * Компонент блоков подборк товаров на главной
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class MainSelection extends CommonComponent
{
    /** @var int */
    private $blockId;
    
    /** @var int */
    private $selectionId;
    
    /** @var array */
    private $selectionProductIdsMap;
    
    /** @var array */
    protected $needModules = [
        'iblock',
        'catalog',
    ];
    
    protected $exceptionNotifier = false;
    
    /** @var IblockFinder */
    private $iblockFinder;
    
    /** @var IblockPropertyFinder */
    private $iblockPropertyFinder;
    
    /** @var LoggerInterface */
    private $logger;
    
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->iblockFinder = \Natix::$container->get(IblockFinder::class);
        $this->iblockPropertyFinder = \Natix::$container->get(IblockPropertyFinder::class);
        $this->logger = \Natix::$container->get(LoggerInterface::class);
    }

    protected function configurate()
    {
        $this->arParams['CACHE_TYPE'] = 'Y';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';
        $this->arParams['BLOCK_ID'] = $this->arParams['BLOCK_ID'] ? (int)$this->arParams['BLOCK_ID'] : null;
        $this->arParams['SELECTION_ID'] = $this->arParams['SELECTION_ID'] ? (int)$this->arParams['SELECTION_ID'] : null;
        
        $this->blockId = $this->arParams['BLOCK_ID'];
        $this->selectionId = $this->arParams['SELECTION_ID'];

        $this->addCacheAdditionalId($this->blockId);
        $this->addCacheAdditionalId($this->selectionId);
    }

    /**
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    protected function executeMain()
    {
        if (!$this->blockId) {
            throw new \RuntimeException('Не передан идентификатор блока');
        }

        try {
            $this->arResult['BLOCK'] = $this->getBlockData();
            $this->arResult['PRODUCT_IDS'] = $this->getProductIds();
            $this->arResult['SELECTIONS'] = $this->getSelections();
        } catch (\Exception $exception) {
            $this->logger->error(sprintf(
                'Ошибка в компоненте вывода блока подборки товаров на главной: %s',
                $exception->getMessage()
            ));
            return false;
        }        
    }

    /**
     * Возвращает данные блока подборки на главной странице. Фактически блок - это раздел в инфоблоке.
     * @return array
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Exception
     */
    protected function getBlockData(): array
    {
        $block = \CIBlockSection::GetList(
            ['ID' => 'ASC'],
            [
                'IBLOCK_ID' => $this->iblockFinder->selectionMain(),
                'ID' => $this->blockId,
                'ACTIVE' => 'Y',
            ],
            false,
            [
                'IBLOCK_ID',
                'ID',
                'NAME',
                'UF_LINK',
            ],
            ['nTopCount' => 1]
        )->Fetch();
        
        if (!$block) {
            throw new \Exception(sprintf(
                'По id "%s" не найден блок подборки',
                $this->blockId
            ));
        }
        
        return $block;
    }

    /**
     * Возвращает идентификаторы товаров в подборке
     * @return array
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    protected function getProductIds(): array
    {
        $productIds = [];
        if ($this->selectionId > 0) {
            $iterator = \CIBlockElement::GetProperty(
                $this->iblockFinder->selectionMain(),
                $this->selectionId,
                'ID', 'ASC',
                ['CODE' => 'PRODUCTS']
            );

            while ($item = $iterator->Fetch()) {
                if ($item['VALUE'] > 0) {
                    $productIds[] = (int)$item['VALUE'];
                }
            }
            
            $this->selectionProductIdsMap[$this->selectionId] = $productIds;
        } else {
            $propId = $this->iblockPropertyFinder->selectionMaimProductsId();
            
            $iterator = \CIBlockElement::GetPropertyValues(
                $this->iblockFinder->selectionMain(),
                [
                    'IBLOCK_SECTION_ID' => $this->blockId,
                    'ACTIVE' => 'Y',
                ],
                false,
                ['ID' => $propId]
            );
            
            while ($item = $iterator->Fetch()) {
                foreach ($item[$propId] as $value) {
                    if ($value > 0) {
                        $productIds[] = $value;
                    }
                }

                $this->selectionProductIdsMap[$item['IBLOCK_ELEMENT_ID']] = $item[$propId];
            }

            $productIds = array_unique($productIds);
        }
        
        return $productIds;
    }

    /**
     * Возвращает доступные подборки, по которым в дальнейшем будет происходить фильтрация
     * @return array
     */
    protected function getSelections(): array
    {
        $result = ElementTable::query()
            ->setSelect(['ID', 'NAME'])
            ->setFilter([
                'IBLOCK_SECTION_ID' => $this->blockId,
                'ACTIVE' => 'Y',
            ])
            ->exec()
            ->fetchAll();
        
        foreach ($result as $key => $item) {
            $result[$key]['JSON_PARAMS'] = [
                'FILTER' => [
                    '@ID' => $this->selectionProductIdsMap[$item['ID']] ?? []
                ],
                'ELEMENT_PER_PAGE' => 30,
            ];
        }
        
        return $result;
    }
}
