<?php

namespace classes;

/**
 * Class GoogleCsvFile класс для работы с csv-файлом Google
 *
 * @package classes
 */
class GoogleCsvFile extends _CsvFileBase
{
    protected array $csvData = [];

    protected static string $csvFile = '';

    protected static string $csvDelimiter = ',';

    public function __construct()
    {
        parent::__construct();

        $this->csvData[] = ['ID', 'ID2', 'Item title', 'Final URL', 'Image URL', 'Item subtitle', 'Item description', 'Item category', 'Price', 'Sale price'];

        self::$csvFile = self::$config['csvGoogleFeed'];
    }

    /**
     * Добавить данные для 1й строки в csv-файл
     *
     * @param array $productData
     */
    public function addProductToFile(array $productData): void
    {
        $siteURL = self::$config['local']['siteURL'];

        $price = $productData['price']['PRICE']['PRICE'];

        $sale_price = $productData['price']['DISCOUNT_PRICE'];

        $sale_price = ($sale_price and $sale_price < $price) ? "$sale_price RUB" : '';

        $csvLineData = [
            $productData['fields']['ID'],
            '',
            ProductsHelper::getProductTitle($productData),
            $siteURL.$productData['fields']['DETAIL_PAGE_URL'],
            $siteURL.$productData['images']['image']['SRC'],
            '',
            ProductsHelper::getProductDescription($productData),
            $productData['fields']['categoryId'],
            "$price RUB",
            $sale_price,
        ];

        $this->csvData[] = $csvLineData;
    }
}
