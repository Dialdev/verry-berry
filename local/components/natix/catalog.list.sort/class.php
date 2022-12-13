<?php

namespace Natix\Component;

use Bitrix\Main\Web\Uri;

/**
 * Компонент вывода блока сортировки в списке товаров
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CatalogListSort extends CommonComponent
{
    public const DEFAULT_SORT_FIELD = 'SORT';
    public const DEFAULT_SORT_ORDER = 'ASC';
    public const REQUEST_SORT_FIELD_PARAM = 'sort_field';
    public const REQUEST_SORT_ORDER_PARAM = 'sort_order';
    
    /** @var array */
    protected $needModules = [];
    
    /** @var bool */
    protected $cacheTemplate = false;

    /** @var array */
    protected $allowSortFields = [
        'price_desc' => [
            'name' => 'Цена, сначала выше',
            self::REQUEST_SORT_FIELD_PARAM => 'PRICE',
            self::REQUEST_SORT_ORDER_PARAM => 'DESC',
        ],
        'price_asc' => [
            'name' => 'Цена, сначала ниже',
            self::REQUEST_SORT_FIELD_PARAM => 'PRICE',
            self::REQUEST_SORT_ORDER_PARAM => 'ASC',
        ],
        'size_desc' => [
            'name' => 'Размер, сначала больше',
            self::REQUEST_SORT_FIELD_PARAM => 'PROPERTY_SIZE',
            self::REQUEST_SORT_ORDER_PARAM => 'DESC',
        ],
        'size_asc' => [
            'name' => 'Размер, сначала меньше',
            self::REQUEST_SORT_FIELD_PARAM => 'PROPERTY_SIZE',
            self::REQUEST_SORT_ORDER_PARAM => 'ASC',
        ],
        'sort_asc' => [
            'name' => 'Популярность, сначала интересные',
            self::REQUEST_SORT_FIELD_PARAM => 'SORT',
            self::REQUEST_SORT_ORDER_PARAM => 'ASC',
        ],
        'sort_desc' => [
            'name' => 'Популярность, сначала неинтересные',
            self::REQUEST_SORT_FIELD_PARAM => 'SORT',
            self::REQUEST_SORT_ORDER_PARAM => 'DESC',
        ],
    ];

    protected function configurate(): void
    {
        $this->arParams['SORT_FIELD'] = trim($this->arParams['SORT_FIELD']) ?? self::DEFAULT_SORT_FIELD;
        $this->arParams['SORT_ORDER'] = trim($this->arParams['SORT_ORDER']) ?? self::DEFAULT_SORT_ORDER;
        
        $this->setResultCacheKeys(md5(
            sprintf('%s%s', $this->arParams['SORT_FIELD'], $this->arParams['SORT_ORDER'])
        ));
    }
    
    protected function executeMain(): void
    {
        $this->prepareSessionSort();
        $this->arResult['ITEMS'] = $this->getItems();
    }

    /**
     * Подготавливает параметры сортировки в сессии пользователя
     */
    protected function prepareSessionSort(): void
    {
        if (!isset($_SESSION['SORT_FIELD']) || empty($_SESSION['SORT_FIELD'])) {
            $_SESSION['SORT_FIELD'] = self::DEFAULT_SORT_FIELD;
        }
        
        if (!isset($_SESSION['SORT_ORDER']) || empty($_SESSION['SORT_ORDER'])) {
            $_SESSION['SORT_ORDER'] = self::DEFAULT_SORT_ORDER;
        }
        
        if (
            isset($this->request[self::REQUEST_SORT_FIELD_PARAM])
            && !empty($this->request[self::REQUEST_SORT_FIELD_PARAM])
        ) {
            $_SESSION['SORT_FIELD'] = $this->request[self::REQUEST_SORT_FIELD_PARAM];
        }

        if (
            isset($this->request[self::REQUEST_SORT_ORDER_PARAM])
            && !empty($this->request[self::REQUEST_SORT_ORDER_PARAM])
        ) {
            $_SESSION['SORT_ORDER'] = $this->request[self::REQUEST_SORT_ORDER_PARAM];
        }
        
        $this->arResult['SORT_FIELD'] = $_SESSION['SORT_FIELD'];
        $this->arResult['SORT_ORDER'] = $_SESSION['SORT_ORDER'];
    }
    
    protected function getItems(): array
    {
        $items = [];
        
        foreach ($this->allowSortFields as $key => $field) {
            $uri = new Uri($this->request->getRequestUri());
            
            $uri->addParams([
                self::REQUEST_SORT_FIELD_PARAM => $field[self::REQUEST_SORT_FIELD_PARAM],
                self::REQUEST_SORT_ORDER_PARAM => $field[self::REQUEST_SORT_ORDER_PARAM],
            ]);
            
            $isSelected = (
                $field[self::REQUEST_SORT_FIELD_PARAM] === $this->arResult['SORT_FIELD']
                && $field[self::REQUEST_SORT_ORDER_PARAM] === $this->arResult['SORT_ORDER']
            );
            
            $items[] = [
                'name' => $field['name'],
                'url' => $uri->getUri(),
                self::REQUEST_SORT_FIELD_PARAM => $field[self::REQUEST_SORT_FIELD_PARAM],
                self::REQUEST_SORT_ORDER_PARAM => $field[self::REQUEST_SORT_ORDER_PARAM],
                'is_selected' => $isSelected,
            ];
        }
        
        return $items;
    }
}
