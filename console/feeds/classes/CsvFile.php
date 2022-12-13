<?php

namespace classes;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Class CsvFile класс для работы с csv-файлом
 *
 * @package classes
 */
class CsvFile extends _CsvFileBase
{
    protected array $csvData = [];

    protected static string $csvFile = '';

    protected static string $csvDelimiter = ';';

    public function __construct()
    {
        parent::__construct();

        $this->csvData[] = ['id', 'title', 'description', 'availability', 'condition', 'price', 'link', 'image_link', 'brand'];

        self::$csvFile = self::$config['csvFeed'];
    }

    /**
     * Добавить данные для 1й строки в csv-файл
     *
     * @param array $productData
     */
    public function addProductToFile(array $productData): void
    {
        $siteURL = self::$config['local']['siteURL'];

        $csvLineData = [
            $productData['fields']['ID'],
            ProductsHelper::getProductTitle($productData),
            ProductsHelper::getProductDescription($productData),
            'in stock',
            'new',
            $productData['price']['PRICE']['PRICE'],
            $siteURL.$productData['fields']['DETAIL_PAGE_URL'],
            $siteURL.$productData['images']['image']['SRC'],
            'Very Berry Lab',
        ];

        $this->csvData[] = $csvLineData;
    }

    /**
     * Конвертация csv-файла в Exel
     *
     * @param string $csvFile
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function convertCsvToExel(string $csvFile): string
    {
        $reader = IOFactory::createReader('Csv');

        $reader->setDelimiter(';');

        $objPHPExcel = $reader->load($csvFile);

        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');

        $exelFile = dirname($csvFile).DIRECTORY_SEPARATOR.pathinfo($csvFile, PATHINFO_FILENAME).'.xlsx';

        $objWriter->save($exelFile);

        self::prints("Сконвертирован csv-файл '$csvFile' в xls-файл '$exelFile'");

        return $exelFile;
    }
}
