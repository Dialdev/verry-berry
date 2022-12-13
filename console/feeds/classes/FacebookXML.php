<?php

namespace classes;

/**
 * Class FacebookXML класс для работы с XML-фидом Facebook-а
 *
 * @package classes
 */
class FacebookXML extends _XML
{
    protected static ?\SimpleXMLElement $feed = null;

    protected static string $namespace = '';

    protected static string $siteUrl = '';

    public function __construct()
    {
        parent::__construct();

        if (!self::$feed)
            self::createNewFeedObj();
    }

    /**
     * Создать новый объект XML-фида
     *
     * @return \SimpleXMLElement
     */
    protected static function createNewFeedObj(): \SimpleXMLElement
    {
        self::$feed = new \SimpleXMLElement(self::$config['template'], null, true);

        self::$namespace = self::$feed->getDocNamespaces()['g'];

        self::$siteUrl = self::$feed->channel->link;

        return self::$feed;
    }

    /**
     * Добавить товар в фид
     *
     * @param array $productData
     * @param bool  $exclude
     */
    public function addProductToFeed(array $productData, bool $exclude = false): void
    {
        $xmlProductsIds = self::createXmlProductsIds($productData);

        foreach ($xmlProductsIds as $Id) {
            $price = $productData['price']['PRICE']['PRICE'];

            $sale_price = $productData['price']['DISCOUNT_PRICE'];

            $item_group_id = self::getGroupId($productData);

            $item = self::$feed->channel->addChild('item');

            $item->addChild('id', $Id, self::$namespace);

            if ($item_group_id)
                $item->addChild('item_group_id', $item_group_id, self::$namespace);

            $item->addChild('link', self::$siteUrl.$productData['fields']['DETAIL_PAGE_URL'], self::$namespace);

            $item->addChild('title', ProductsHelper::getProductTitle($productData), self::$namespace);

            $item->addChild('price', "$price RUB", self::$namespace);

            if ($sale_price and $sale_price < $price)
                $item->addChild('sale_price', "$sale_price RUB", self::$namespace);

            $description = ProductsHelper::getProductDescription($productData);

            $item->addChild('description', $description, self::$namespace);

            $item->addChild('image_link', self::$siteUrl.$productData['images']['image']['SRC'], self::$namespace);

            foreach ($productData['images']['additionalImages'] as $image)
                $item->addChild('additional_image_link', self::$siteUrl.$image, self::$namespace);

            $item->addChild('availability', 'in stock', self::$namespace);

            if ($productData['size']) {
                $item->addChild('size', $productData['size'], self::$namespace);

                $item->addChild('custom_label_0', $productData['size'], self::$namespace);
            }

            $item->addChild('brand', 'veryberrylab', self::$namespace);

            if (!$exclude)
                $item->addChild('age_group', 'all ages', self::$namespace);

            $item->addChild('gender', 'unisex', self::$namespace);

            $item->addChild('condition', 'new', self::$namespace);

            $item->addChild('google_product_category', 2559, self::$namespace);

            $item->addChild('product_type', $productData['fields']['categorySingle'], self::$namespace);
        }
    }

    /**
     * Сохранить фид в файл
     *
     * @param string|null $file
     */
    public function saveFeed(string $file = null): void
    {
        $file = $file ?: self::$config['facebookFeed'];

        if (self::saveXML(self::$feed, $file))
            self::prints("Фид успешно сохранён в '$file'");
        else
            self::prints('Ошибка сохранения фида');
    }

}
