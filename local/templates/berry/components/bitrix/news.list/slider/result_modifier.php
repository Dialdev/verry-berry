<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

foreach ($arResult['ITEMS'] as $key => $item) {
    if (isset($item['PREVIEW_PICTURE']['ID'])) {
        $smPicture = \CFile::ResizeImageGet(
            $item['PREVIEW_PICTURE']['ID'],
            ['width' => 892, 'height' => 334],
            BX_RESIZE_IMAGE_PROPORTIONAL,
            true
        );
        
        $arResult['ITEMS'][$key]['PREVIEW_PICTURE']['SM'] = $smPicture;

        $mdPicture = \CFile::ResizeImageGet(
            $item['PREVIEW_PICTURE']['ID'],
            ['width' => 1100, 'height' => 412],
            BX_RESIZE_IMAGE_PROPORTIONAL,
            true
        );

        $arResult['ITEMS'][$key]['PREVIEW_PICTURE']['MD'] = $mdPicture;
    }
}
