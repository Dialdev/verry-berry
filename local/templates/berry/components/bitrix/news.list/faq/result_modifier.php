<?php
/**
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Iblock\SectionTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arSections = [];
$arItems = [];
foreach ($arResult['ITEMS'] as $arItem) {
    $arSections[$arItem['IBLOCK_SECTION_ID']] = $arItem['IBLOCK_SECTION_ID'];
    $arItems[$arItem['IBLOCK_SECTION_ID']][] = $arItem;
}

if (!empty($arSections)) {
    $objSections = SectionTable::getList([
        'filter' => [
            '=IBLOCK_ID' => $arParams['IBLOCK_ID'],
            '@ID' => $arSections,
            '=ACTIVE' => 'Y'
        ],
        'order' => [
            'SORT' => 'ASC'
        ]
    ]);

    while ($res = $objSections->fetch()) {
        if (empty($arItems[$res['ID']])) {
            continue;
        }
        $arResult['FAQ_LIST'][] = [
            'SECTION' => $res,
            'ITEMS' => $arItems[$res['ID']]
        ];
    }
}
