<?php

namespace classes;

/**
 * Class FacebookFeed главный класс создания Facebook-фида
 *
 * @package classes
 */
class FacebookFeed extends ProductsHelper
{
    /**
     * Создать фид
     */
    public function create(): void
    {
        self::prints("Начинаем создавать фид для Facebook-а. Ожидаемый файл: ".self::$config['facebookFeed'].'...', true); 
        
        sleep(4);
        
        $feed = new FacebookXML;

        foreach (self::getNextProducts() as $product) {
            $productData = self::getProductData($product);

            $feed->addProductToFeed($productData);
        }

        $feed->saveFeed();
    }
}
