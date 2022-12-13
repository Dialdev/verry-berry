<?php

namespace classes;

/**
 * Class YML класс для работы с YML-фидом
 *
 * @package classes
 */
class YML extends _XML
{
    protected static ?\SimpleXMLElement $feed = null;

    protected static string $siteUrl = '';

    public function __construct()
    {
        parent::__construct();

        if (!self::$feed) {
            self::$feed = new \SimpleXMLElement(self::$config['templateYML'], null, true);

            self::$siteUrl = self::$feed->shop->url;

            self::$feed['date'] = date('Y-m-d H:i:s');
        }
    }

    /**
     * Добавить товар в фид
     *
     * @param array $productData
     */
    public function addProductToFeed(array $productData): void
    {
        $this->addCategoryToFeed($productData);

        $xmlProductsIds = self::createXmlProductsIds($productData);

        foreach ($xmlProductsIds as $id) {
            $group_id = self::getGroupId($productData);

            $offer = $this->createOffer($id, $group_id);

            $this->addDataToOffer($offer, $productData);
        }

    }

    /**
     * Добавить остальные теги (данные) в тег <offer>
     *
     * @param \SimpleXMLElement $offer
     * @param array             $productData
     */
    protected function addDataToOffer(\SimpleXMLElement $offer, array $productData): void
    {
        $price = $productData['price']['PRICE']['PRICE'];

        $sale_price = $productData['price']['DISCOUNT_PRICE'];

        $offer->addChild('url', self::$siteUrl.$productData['fields']['DETAIL_PAGE_URL']);

        $offer->addChild('price', $price);

        if ($sale_price and $sale_price < $price)
            $offer->addChild('saleprice', $sale_price);

        $offer->addChild('currencyId', 'RUR');

        $offer->addChild('categoryId', $productData['fields']['categoryId']);

        $offer->addChild('vendor', 'veryberrylab');

        $offer->addChild('model', ProductsHelper::getProductTitle($productData));

        $offer->addChild('description', ProductsHelper::getProductDescription($productData));

        $offer->addChild('picture', self::$siteUrl.$productData['images']['image']['SRC']);

        $additionalImages = false;

        if ($additionalImages) {
            foreach ($productData['images']['additionalImages'] as $image)
                $offer->addChild('picture', self::$siteUrl.$image);
        }
        
        if ($productData['size']) {
            $param = $offer->addChild('param', $productData['size']);

            $param['name'] = 'Размер';
        }

        $offer->addChild('store', 'true');

        $offer->addChild('pickup', 'true');

        $offer->addChild('delivery', 'true');
    }

    /**
     * Добавить тег <offer> в фид
     *
     * @param int      $id
     * @param int|null $group_id
     * @return \SimpleXMLElement
     */
    protected function createOffer(int $id, ?int $group_id): \SimpleXMLElement
    {
        $offer = self::$feed->shop->offers->addChild('offer');

        $offer['type'] = 'vendor.model';

        $offer['id'] = $id;

        if ($group_id)
            $offer['group_id'] = $group_id;

        $offer['available'] = 'true';

        return $offer;
    }

    /**
     * Добавить категорию в фид
     *
     * @param array $productData
     * @return bool
     */
    protected function addCategoryToFeed(array $productData): bool
    {
        $categoryId = $productData['fields']['categoryId'];

        $category = self::$feed->xpath("/yml_catalog/shop/categories/category[@id='$categoryId']");

        if ($category)
            return false;

        $category = self::$feed->shop->categories->addChild('category', $productData['fields']['category']);

        $category['id'] = $categoryId;

        return true;
    }

    /**
     * Сохранить YML-фид в файл
     */
    public function saveFeed(): void
    {
        $file = self::$config['ymlFeed'];

        $line = self::saveXML(self::$feed, $file) ? "YML-фид успешно сохранён в '$file'" : 'Ошибка сохранения фида';

        self::prints($line);
    }
}
