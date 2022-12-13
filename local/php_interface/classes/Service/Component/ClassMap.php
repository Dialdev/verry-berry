<?php

namespace Natix\Service\Component;

use Natix\Component\CatalogElement;
use Natix\Component\CatalogSetList;
use Natix\Component\MainSelection;

/**
 * Содержит карту соответствий между названиями компонента и классом
 */
class ClassMap
{
    /**
     * Возвращает название класса компонента, если он указан в карте
     * В противном случае вернёт false
     * @param string $componentName
     * @return bool|mixed
     */
    public function getClassNameByComponentName(string $componentName)
    {
        return $this->getClassMap()[$componentName] ?? false;
    }

    public function getClassMap(): array
    {
        return [
            'natix:catalog.set.list' => CatalogSetList::class,
            'natix:main.selection' => MainSelection::class,
            'natix:catalog.element' => CatalogElement::class,
        ];
    }
}
