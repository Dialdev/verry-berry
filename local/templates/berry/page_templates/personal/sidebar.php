<?php $APPLICATION->IncludeComponent(
    'bitrix:menu',
    'personal',
    [
        'ROOT_MENU_TYPE' => 'personal',
        'MENU_CACHE_TYPE' => 'N',
        'MENU_CACHE_TIME' => '3600',
        'MENU_CACHE_USE_GROUPS' => 'Y',
        'MENU_CACHE_GET_VARS' => '',
        'MAX_LEVEL' => '1',
        'CHILD_MENU_TYPE' => '',
        'USE_EXT' => 'Y',
        'DELAY' => 'N',
        'ALLOW_MULTI_SELECT' => 'N',
    ],
    false
); ?>
