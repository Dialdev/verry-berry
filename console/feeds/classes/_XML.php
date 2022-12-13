<?php

namespace classes;

/**
 * Class XML базовый класс для работы с XML
 *
 * @package classes
 */
abstract class _XML extends _Base
{
    /**
     * Сохранить XML-файл
     *
     * @param \SimpleXMLElement $xml
     * @param string            $file
     * @return bool
     */
    public static function saveXML(\SimpleXMLElement $xml, string $file): bool
    {
        $dom = new \DOMDocument;

        $dom->preserveWhiteSpace = false;

        $dom->formatOutput = true;

        $dom->loadXML($xml->asXML());

        return $dom->save($file, LIBXML_NOEMPTYTAG) ? true : false;
    }

    /**
     * Сгенерировать массив с Id-шниками для фида и id-товара и его предложений
     *
     * @param array $productData
     * @return array
     */
    protected static function createXmlProductsIds(array $productData): array
    {
        $xmlProductsIds = [$productData['fields']['ID']];

        if ($productData['offers'])
            $xmlProductsIds = array_merge($xmlProductsIds, array_keys($productData['offers']));

        sort($xmlProductsIds);

        return $xmlProductsIds;
    }

    /**
     * Получить группирующий id-товаров, если есть
     *
     * @param array $productData
     * @return int|null
     */
    protected static function getGroupId(array $productData): ?int
    {
        return $productData['apiProduct'] ? self::createItemGroupId($productData['apiProduct']->combinations) : null;
    }

    /**
     * Создать параметр item_group_id в фиде
     *
     * @param \stdClass $productCombinations
     * @return int
     */
    protected static function createItemGroupId(\stdClass $productCombinations): int
    {
        $itemgGroupId = 0;

        foreach ($productCombinations->sizes as $combination) {
            preg_match('~(\d+)\.~', $combination->article, $matches);

            $itemgGroupId += $matches[1] ?? 0;
        }

        return $itemgGroupId;
    }
}
