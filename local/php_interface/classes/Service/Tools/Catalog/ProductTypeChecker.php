<?php

namespace Natix\Service\Tools\Catalog;

use Bitrix\Catalog\ProductTable;
use Natix\Service\Tools\Catalog\Exception\ProductTypeCheckerException;

/**
 * Определяет тип товара
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ProductTypeChecker
{
    /**
     * Проверяет, что товар является простым
     * 
     * @param int $productId
     *
     * @return bool
     * @throws ProductTypeCheckerException
     */
    public function isTypeProduct(int $productId): bool
    {
        return $this->getProductType($productId) === ProductTable::TYPE_PRODUCT;
    }
    
    /**
     * Проверяет, что товар относится к типу "Комплект"
     *
     * @param int $productId
     *
     * @return bool
     * @throws ProductTypeCheckerException
     */
    public function isTypeSet(int $productId): bool
    {
        return $this->getProductType($productId) === ProductTable::TYPE_SET;
    }

    /**
     * Проверяет, что товар относится к типу "Товар с торговыми предложениями"
     *
     * @param int $productId
     *
     * @return bool
     * @throws ProductTypeCheckerException
     */
    public function isTypeSku(int $productId): bool
    {
        return $this->getProductType($productId) === ProductTable::TYPE_SKU;
    }

    /**
     * Проверяет, что товар относится к типу "Торговое предложение"
     *
     * @param int $productId
     *
     * @return bool
     * @throws ProductTypeCheckerException
     */
    public function isTypeOffer(int $productId): bool
    {
        return $this->getProductType($productId) === ProductTable::TYPE_OFFER;
    }

    /**
     * Возвращает тип товара
     * 
     * @param int $productId
     *
     * @return int
     * @throws ProductTypeCheckerException
     */
    private function getProductType(int $productId): int
    {
        $product = ProductTable::getRow([
            'select' => ['TYPE'],
            'filter' => [
                '=ID' => $productId,
            ],
            'cache' => ['ttl' => 86400],
        ]);
        
        if (!(int)$product['TYPE']) {
            throw new ProductTypeCheckerException(sprintf('Не удалось выяснить тип товара "%s"', $productId));
        }
        
        return (int)$product['TYPE'];
    }
}
