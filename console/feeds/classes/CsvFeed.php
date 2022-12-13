<?php

namespace classes;

/**
 * Class ExelFeed главный класс создания Exel-фида
 *
 * @package classes
 */
class CsvFeed extends ProductsHelper
{
    /**
     * Создать фид
     */
    public function create(): void
    {
        self::prints("\n Начинаем создавать фид csv/exel. Ожидаемый csv-файл: ".self::$config['csvFeed'].'...', true);

        sleep(4);
        
        $file = new CsvFile;

        foreach (self::getNextProducts() as $product) {
            $productData = self::getProductData($product);

            $file->addProductToFile($productData);
        }

        $csvFile = $file->saveCSV();

        $file->convertCsvToExel($csvFile);
    }
}
