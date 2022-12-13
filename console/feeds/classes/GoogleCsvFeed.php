<?php

namespace classes;

/**
 * Class GoogleCsvFeed главный класс создания csv-фида для Google
 *
 * @package classes
 */
class GoogleCsvFeed extends ProductsHelper
{
    /**
     * Создать фид
     */
    public function create(): void
    {
        self::prints("\n Начинаем создавать csv-фид для Google. Ожидаемый файл: ".self::$config['csvGoogleFeed'].'...', true);

        sleep(4);
        
        $file = new GoogleCsvFile;

        foreach (self::getNextProducts() as $product) {
            $productData = self::getProductData($product);

            $file->addProductToFile($productData);
        }

        $file->saveCSV();
    }
}
