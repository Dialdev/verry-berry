<?php

namespace classes;

/**
 * Class GoogleFeed главный класс создания Google-фида
 *
 * @package classes
 */
class GoogleFeed extends ProductsHelper
{
    
    /**
     * Создать фид
     */
    public function create(): void
    {
        self::prints("Начинаем создавать фид для Google-а. Ожидаемый файл: ".self::$config['googleFeed'].'...', true);

        sleep(4);

        $feed = new GoogleXML;

        foreach (self::getNextProducts() as $product) {
            $productData = self::getProductData($product);

            $feed->addProductToFeed($productData);
        }

        $feed->saveFeed();
    }
}
