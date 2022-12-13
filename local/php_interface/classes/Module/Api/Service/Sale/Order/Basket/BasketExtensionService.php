<?php

namespace Natix\Module\Api\Service\Sale\Order\Basket;

use Bitrix\Iblock\ElementTable;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Service\Catalog\Bouquets\Dto\SetQueryParamsDto;
use Natix\Service\Catalog\Bouquets\Entity\ImageEntity;
use Natix\Service\Catalog\Bouquets\Entity\SetEntity;
use Natix\Service\Catalog\Bouquets\Service\Factory\ImageFactory;
use Natix\Service\Catalog\Bouquets\Service\Factory\SetFactory;
use Natix\Service\Tools\Catalog\ProductTypeChecker;
use Slim\Http\Request;

/**
 * Во многих методах результатом является содержимое корзины пользователя,
 * полученное через BasketService::getCurUserPreparedBasket.
 * Иногда возникает необходимость, чтобы этот метод сразу же возвращал информацию о товарах в корзине,
 * полученную из каталога, например размер, цвет упаковки и т.д.
 * Для того, чтобы получить эту информацию нужно передать GET параметр GET_PRODUCTS_DATA=Y
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BasketExtensionService
{
    /**
     * @var SetFactory
     */
    private $setFactory;

    /**
     * @var ProductTypeChecker
     */
    private $productTypeChecker;

    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    public function __construct(
        SetFactory $setFactory,
        ProductTypeChecker $productTypeChecker,
        IblockFinder $iblockFinder,
        ImageFactory $imageFactory
    ) {
        $this->setFactory = $setFactory;
        $this->productTypeChecker = $productTypeChecker;
        $this->iblockFinder = $iblockFinder;
        $this->imageFactory = $imageFactory;
    }

    /**
     * @param array $basketData Данные корзины полученные с помощью BasketService::getCurUserPreparedBasket
     * @param Request $request
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\BerriesFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\BouquetFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PackingFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\SetFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException
     * @throws \Natix\Service\Tools\Catalog\Exception\ProductTypeCheckerException
     */
    public function extendBasketIfRequired(array $basketData, Request $request)
    {
        // Расширенная информация о товарах в корзине
        if (
            $request->getParam('GET_PRODUCTS_DATA') === 'Y'
            && count($basketData['BASKET_ITEMS']) > 0
        ) {
            $extendParams = $request->getParam('GET_PRODUCTS_PARAMS');
            if (!is_array($extendParams)) {
                $extendParams = [];
            }

            $basketData = $this->extendBasketProductsData($basketData, $extendParams);
        }


        return $basketData;
    }

    /**
     * @param array $basketData
     * @param array $extendParams
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\BerriesFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\BouquetFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PackingFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\PriceFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\SetFactoryException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException
     * @throws \Natix\Service\Tools\Catalog\Exception\ProductTypeCheckerException
     */
    private function extendBasketProductsData(array $basketData, array $extendParams): array
    {
        $basketProductIds = array_column($basketData['BASKET_ITEMS'], 'PRODUCT_ID');

        $basketSetTypeProductIds = [];
        
        foreach ($basketProductIds as $key => $productId) {
            if ($this->productTypeChecker->isTypeSet((int)$productId)) {
                $basketSetTypeProductIds[] = $productId;
            }
        }
        
        $basketOtherTypeProductIds = array_diff($basketProductIds, $basketSetTypeProductIds);

        $setQueryParamsDto = new SetQueryParamsDto(
            ['ID' => $basketSetTypeProductIds],
            'ID',
            'ASC',
            null,
            null
        );

        $setEntityCollection = $this->setFactory->buildByParams($setQueryParamsDto, null);

        foreach ($basketData['BASKET_ITEMS'] as $key => $basketItem) {
            if ($setEntityCollection->has((int)$basketItem['PRODUCT_ID'])) {
                /** @var SetEntity $set */
                $set = $setEntityCollection->get((int)$basketItem['PRODUCT_ID']);

                $basketData['BASKET_ITEMS'][$key]['PRODUCT_DATA'] = SetEntity::toState($set);
            }
            
            if (in_array($basketItem['PRODUCT_ID'], $basketOtherTypeProductIds)) {
                $basketData['BASKET_ITEMS'][$key]['PRODUCT_DATA'] = $this->getProductData((int)$basketItem['PRODUCT_ID']);
            }
        }

        return $basketData;
    }

    /**
     * Возвращает информацию о простом товаре или товаре с ТП
     *
     * @param int $productId
     *
     * @return array
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException
     */
    private function getProductData(int $productId): array
    {
        if ($productId <= 0) {
            throw new \RuntimeException('Не передан $productId');
        }
        
        $element = ElementTable::query()
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
                'PREVIEW_PICTURE',
            ])
            ->setFilter([
                '=IBLOCK_ID' => [
                    $this->iblockFinder->catalog(),
                    $this->iblockFinder->offers(),
                ],
                '=ID' => $productId
            ])
            ->exec()
            ->fetch();
        
        if (!$element) {
            throw new \RuntimeException(sprintf('не найден товар по id "%s"', $productId));
        }
        
        if ($element['PREVIEW_PICTURE']) {
            $imageEntity = $this->imageFactory->build((int)$element['PREVIEW_PICTURE'], true, 131,98);
        }
                
        $data = [
            'id' => (int)$element['ID'],
            'name' => $element['NAME'],
            'card_name' => $element['NAME'],
            'code' => $element['CODE'],
            'url' => sprintf('/product/%s/', $element['CODE']),
            'image' => $imageEntity !== null ? ImageEntity::toState($imageEntity) : []
        ];
        
        return $data;
    }
}
