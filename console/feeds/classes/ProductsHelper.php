<?php

namespace classes;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class ProductsHelper класс для работы с товарами
 *
 * @package classes
 */
class ProductsHelper extends _Base
{
    /**
     * Получить необходимые данные продукта
     *
     * @param array $product
     * @return array
     * @throws GuzzleException
     */
    protected static function getProductData(array $product): array
    {
        $fields = &$product['fields'];

        $properties = &$product['properties'];

        foreach ($fields['PROPERTY_13'] as $imageFileID)
            $additionalImages[] = \CFile::GetPath($imageFileID);

        $size = $properties['SIZE']['VALUE'];

        if ($size) {
            $size = \CIBlockElement::GetList(false,
                ['IBLOCK_ID' => 3, 'ID' => $size],
                false,
                ['nPageSize' => 1],
                ['NAME']
            )->GetNextElement()->GetFields()['NAME'];
        }

        $apiProduct = ($fields['category'] == 'Букеты') ? self::getProductByAPI($fields['IBLOCK_SECTION_ID'], $fields['ID']) : null;

        return [
            'price' => \CCatalogProduct::GetOptimalPrice($fields['ID'], 1),

            'size' => $size ?? null,

            'fields' => $fields,

            'properties' => $properties,

            'offers' => $product['offers'],

            'images' => [
                'image' => \CFile::GetFileArray($fields['PREVIEW_PICTURE']),

                'additionalImages' => $additionalImages ?? [],
            ],

            'apiProduct' => $apiProduct,
        ];
    }

    /**
     * Получить следующий продукт, пока не будут перебраны все
     *
     * @return \Generator
     */
    protected static function getNextProducts(): \Generator
    {
        $i = $z = 0;

        $iBlockId = self::$config['iBlockId'];

        do {
            $i++;

            $products = \CIBlockElement::GetList(false,
                ['ACTIVE' => 'Y', 'IBLOCK_ID' => $iBlockId],
                false,
                ['nPageSize' => 100, 'iNumPage' => $i],
                ['*', 'PREVIEW_PICTURE', 'PROPERTY_*']
            );

            while ($product = $products->GetNextElement()) {
                $z++;

                $resultProduct['fields'] = $fields = $product->GetFields();

                $resultProduct['properties'] = $product->GetProperties();

                $productID = $fields['ID'];

                Console::print("Обработка продукта '$fields[NAME]'. ID: $productID. URL: '$fields[DETAIL_PAGE_URL]'. Это товар $z из {$products->NavRecordCount}");

                unset($product);

                $category = self::getProductMainCategory($resultProduct, $iBlockId);

                $resultProduct['fields']['categoryId'] = $category['ID'];
                
                $resultProduct['fields']['categorySingle'] = self::convertCategoryNameToSingle($category)['NAME'];

                $resultProduct['fields']['category'] = self::convertCategoryNameToSingle($category)['~NAME'];

                $resultProduct['offers'] = current(\CCatalogSKU::getOffersList([$productID], 0, [], ['*']));

                yield $resultProduct;
            }

        } while ($products->NavPageCount > $i);

        return null;
    }

    /**
     * Получить продукт по API
     *
     * @param int $sectionId
     * @param int $setId
     * @return \stdClass
     * @throws GuzzleException
     */
    protected static function getProductByAPI(int $sectionId, int $setId): \stdClass
    {
        $client = new Client([
            'connect_timeout' => $timeout = 60,
            'timeout'         => $timeout,
            'allow_redirects' => false,
            'http_errors'     => true,
            'verify'          => false,
        ]);

        $site = self::$config['local']['siteURL'];

        $url = "$site/api/v1/catalog/product/set/?sectionId=$sectionId&setId=$setId";

        Console::print("Отправка запроса к URL $url...");

        $response = $client->request('GET', $url);

        $response = json_decode($response->getBody()->getContents())->data;

        self::setArticlesInSizes($response);

        return $response;
    }

    /**
     * Добавить артикли в комбинации размеров
     *
     * @param \stdClass $apiResponse
     */
    protected static function setArticlesInSizes(\stdClass $apiResponse): void
    {
        foreach ($apiResponse->combinations->sizes as $combination) {
            $combination->article = null;

            if (!$combination->setId)
                continue;

            $article = \CIBlockElement::GetList(false,
                ['IBLOCK_ID' => self::$config['iBlockId'], 'ID' => $combination->setId],
                false,
                ['nTopCount' => 1,],
                ['*', 'PROPERTY_*']
            )->GetNextElement()->GetFields()['PROPERTY_12'];

            $combination->article = $article;
        }
    }

    /**
     * Получить верхнюю категорию товара
     *
     * @param array $product
     * @param int   $iBlockId
     * @return array
     */
    protected static function getProductMainCategory(array $product, int $iBlockId): array
    {
        return \CIBlockSection::GetNavChain($iBlockId, $product['fields']['IBLOCK_SECTION_ID'])->GetNext();
    }

    /**
     * Конвертировать имя категории в единичное число
     *
     * @param array $category
     * @return array
     */
    protected static function convertCategoryNameToSingle(array $category)
    {
        $replaceArray = [
            'Букеты'             => 'Букет',
            'Шкатулки с цветами' => 'Шкатулка с цветами',
            'Наборы ягод'        => 'Набор ягод',
            'Шляпные коробки'    => 'Шляпная коробка',
            'Букеты с розами'    => 'Букет с розами',
            'Корзины'            => 'Корзина',
            'Шарики'             => 'Шарик',
        ];

        $category['NAME'] = $replaceArray[$category['NAME']] ?? $category['NAME'];

        return $category;
    }

    /**
     * Получить и обработать название товара, для фида
     *
     * @param array $productData
     * @return string
     */
    public static function getProductTitle(array $productData): string
    {
        return trim($productData['fields']['categorySingle'].' '.$productData['fields']['NAME']);
    }

    /**
     * Получить и обработать описание товара, для фида
     *
     * @param array $productData
     * @return string
     */
    public static function getProductDescription(array $productData): string
    {
        $description = trim(preg_replace('~\s+~u', ' ', trim(strip_tags($productData['fields']['~PROPERTY_40']['TEXT']))));

        $description = preg_replace('~&\w+;~u', '', $description);

        return $description;
    }
}
