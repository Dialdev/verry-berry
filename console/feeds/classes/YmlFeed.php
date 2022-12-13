<?php

namespace classes;

/**
 * Class YmlFeed главный класс создания Yml-фида
 *
 * @package classes
 */
class YmlFeed extends ProductsHelper
{
    /**
     * Создать фид
     */
    public function create(): void
    {
        self::prints("Начинаем создавать YML-фид. Ожидаемый файл: ".self::$config['ymlFeed'].'...', true);

        sleep(4);

        $feed = new YML;

        foreach (self::getNextProducts() as $product) {
            $productData = self::getProductData($product);

            $feed->addProductToFeed($productData);
        }

        $feed->saveFeed();
    }
}
