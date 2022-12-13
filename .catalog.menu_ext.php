<?php

use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

/** @var IblockFinder $iblockFinder */
$iblockFinder = \Natix::$container->get(IblockFinder::class);

$aMenuLinksExt=$APPLICATION->IncludeComponent(
    'bitrix:menu.sections',
    '',
    [
        'IS_SEF' => 'Y',
        'SEF_BASE_URL' => '/catalog/',
        'SECTION_PAGE_URL' => '#SECTION_CODE#/',
        'DETAIL_PAGE_URL' => '/product/#ELEMENT_CODE#/',
        'IBLOCK_TYPE' => 'catalogs',
        'IBLOCK_ID' => $iblockFinder->catalog(),
        'DEPTH_LEVEL' => 1,
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => 3600
    ],
    false
);

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
