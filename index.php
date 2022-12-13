<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetPageProperty("description", "Интернет-магазин цветов, шоколадно-клубничных букетов с ягодами с быстрой доставкой по Москве и СПБ. 🚚 Мы изготавливаем букеты из клубники в шоколаде, конфеты ручной работы, подарочные корзины, шкатулки с клубникой в шоколаде, эксклюзивные букеты с цветам, фруктовые букеты в шляпной коробке, которые можно купить на нашем сайте.");
$APPLICATION->SetPageProperty("title", "🍓 Интернет-магазин клубничных ягодных букетов с цветами и шоколадом - купить с доставкой по Москве и СПБ 🍓  | Very Berry ");
$APPLICATION->SetTitle("Интернет-магазин Very Berry");
$APPLICATION->AddViewContent('body-css-class', 'index');
?><div class="topBlock">
    <div class="topBlock__inner">
        <?php $APPLICATION->IncludeComponent(
            'bitrix:news.list',
            'slider',
            [
                'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                'ADD_SECTIONS_CHAIN' => 'N',
                'AJAX_MODE' => 'N',
                'AJAX_OPTION_ADDITIONAL' => '',
                'AJAX_OPTION_HISTORY' => 'N',
                'AJAX_OPTION_JUMP' => 'N',
                'AJAX_OPTION_STYLE' => 'Y',
                'CACHE_FILTER' => 'N',
                'CACHE_GROUPS' => 'Y',
                'CACHE_TIME' => '36000000',
                'CACHE_TYPE' => 'A',
                'CHECK_DATES' => 'Y',
                'DETAIL_URL' => '',
                'DISPLAY_BOTTOM_PAGER' => 'Y',
                'DISPLAY_DATE' => 'N',
                'DISPLAY_NAME' => 'Y',
                'DISPLAY_PICTURE' => 'Y',
                'DISPLAY_PREVIEW_TEXT' => 'N',
                'DISPLAY_TOP_PAGER' => 'N',
                'FIELD_CODE' => [
                    0 => 'ID',
                    1 => 'NAME',
                    2 => 'PREVIEW_PICTURE',
                ],
                'FILTER_NAME' => '',
                'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                'IBLOCK_ID' => '6',
                'IBLOCK_TYPE' => 'content',
                'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                'INCLUDE_SUBSECTIONS' => 'N',
                'MESSAGE_404' => '',
                'NEWS_COUNT' => '20',
                'PAGER_BASE_LINK_ENABLE' => 'N',
                'PAGER_DESC_NUMBERING' => 'N',
                'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                'PAGER_SHOW_ALL' => 'N',
                'PAGER_SHOW_ALWAYS' => 'N',
                'PAGER_TEMPLATE' => '.default',
                'PAGER_TITLE' => 'Новости',
                'PARENT_SECTION' => '',
                'PARENT_SECTION_CODE' => '',
                'PREVIEW_TRUNCATE_LEN' => '',
                'PROPERTY_CODE' => [
                    0 => 'LINK',
                    1 => 'COLOR',
                    2 => 'THEME',
                ],
                'SET_BROWSER_TITLE' => 'N',
                'SET_LAST_MODIFIED' => 'N',
                'SET_META_DESCRIPTION' => 'N',
                'SET_META_KEYWORDS' => 'N',
                'SET_STATUS_404' => 'N',
                'SET_TITLE' => 'N',
                'SHOW_404' => 'N',
                'SORT_BY1' => 'ACTIVE_FROM',
                'SORT_BY2' => 'SORT',
                'SORT_ORDER1' => 'DESC',
                'SORT_ORDER2' => 'ASC',
                'STRICT_SECTION_CHECK' => 'N',
                'COMPONENT_TEMPLATE' => 'slider',
            ],
            false
        );?>

        <?php $APPLICATION->IncludeComponent(
            'bitrix:news.list',
            'main_banners',
            [
                'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                'ADD_SECTIONS_CHAIN' => 'N',
                'AJAX_MODE' => 'N',
                'AJAX_OPTION_ADDITIONAL' => '',
                'AJAX_OPTION_HISTORY' => 'N',
                'AJAX_OPTION_JUMP' => 'N',
                'AJAX_OPTION_STYLE' => 'Y',
                'CACHE_FILTER' => 'N',
                'CACHE_GROUPS' => 'Y',
                'CACHE_TIME' => '36000000',
                'CACHE_TYPE' => 'A',
                'CHECK_DATES' => 'Y',
                'DETAIL_URL' => '',
                'DISPLAY_BOTTOM_PAGER' => 'Y',
                'DISPLAY_DATE' => 'N',
                'DISPLAY_NAME' => 'Y',
                'DISPLAY_PICTURE' => 'Y',
                'DISPLAY_PREVIEW_TEXT' => 'N',
                'DISPLAY_TOP_PAGER' => 'N',
                'FIELD_CODE' => [
                    0 => 'ID',
                    1 => 'NAME',
                    2 => 'PREVIEW_PICTURE',
                ],
                'FILTER_NAME' => '',
                'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                'IBLOCK_ID' => '7',
                'IBLOCK_TYPE' => 'content',
                'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                'INCLUDE_SUBSECTIONS' => 'N',
                'MESSAGE_404' => '',
                'NEWS_COUNT' => '2',
                'PAGER_BASE_LINK_ENABLE' => 'N',
                'PAGER_DESC_NUMBERING' => 'N',
                'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                'PAGER_SHOW_ALL' => 'N',
                'PAGER_SHOW_ALWAYS' => 'N',
                'PAGER_TEMPLATE' => '.default',
                'PAGER_TITLE' => 'Новости',
                'PARENT_SECTION' => '',
                'PARENT_SECTION_CODE' => '',
                'PREVIEW_TRUNCATE_LEN' => '',
                'PROPERTY_CODE' => [
                    0 => 'LINK',
                    1 => 'ICON',
                    2 => 'THEME',
                ],
                'SET_BROWSER_TITLE' => 'N',
                'SET_LAST_MODIFIED' => 'N',
                'SET_META_DESCRIPTION' => 'N',
                'SET_META_KEYWORDS' => 'N',
                'SET_STATUS_404' => 'N',
                'SET_TITLE' => 'N',
                'SHOW_404' => 'N',
                'SORT_BY1' => 'SORT',
                'SORT_BY2' => 'SORT',
                'SORT_ORDER1' => 'ASC',
                'SORT_ORDER2' => 'ASC',
                'STRICT_SECTION_CHECK' => 'N',
                'COMPONENT_TEMPLATE' => 'main_banners',
            ],
            false
        );?>
    </div>
</div>

<div class="container">
    <div class="flowsBlock">
        <?php $APPLICATION->IncludeComponent(
            'bitrix:news.list',
            'main_links',
            [
                'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                'ADD_SECTIONS_CHAIN' => 'N',
                'AJAX_MODE' => 'N',
                'AJAX_OPTION_ADDITIONAL' => '',
                'AJAX_OPTION_HISTORY' => 'N',
                'AJAX_OPTION_JUMP' => 'N',
                'AJAX_OPTION_STYLE' => 'Y',
                'CACHE_FILTER' => 'N',
                'CACHE_GROUPS' => 'Y',
                'CACHE_TIME' => '36000000',
                'CACHE_TYPE' => 'A',
                'CHECK_DATES' => 'Y',
                'DETAIL_URL' => '',
                'DISPLAY_BOTTOM_PAGER' => 'Y',
                'DISPLAY_DATE' => 'N',
                'DISPLAY_NAME' => 'Y',
                'DISPLAY_PICTURE' => 'Y',
                'DISPLAY_PREVIEW_TEXT' => 'N',
                'DISPLAY_TOP_PAGER' => 'N',
                'FIELD_CODE' => [
                    0 => 'ID',
                    1 => 'NAME',
                    2 => 'PREVIEW_TEXT',
                ],
                'FILTER_NAME' => '',
                'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                'IBLOCK_ID' => '20',
                'IBLOCK_TYPE' => 'content',
                'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                'INCLUDE_SUBSECTIONS' => 'N',
                'MESSAGE_404' => '',
                'NEWS_COUNT' => '6',
                'PAGER_BASE_LINK_ENABLE' => 'N',
                'PAGER_DESC_NUMBERING' => 'N',
                'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                'PAGER_SHOW_ALL' => 'N',
                'PAGER_SHOW_ALWAYS' => 'N',
                'PAGER_TEMPLATE' => '.default',
                'PAGER_TITLE' => 'Новости',
                'PARENT_SECTION' => '',
                'PARENT_SECTION_CODE' => '',
                'PREVIEW_TRUNCATE_LEN' => '',
                'PROPERTY_CODE' => [
                    0 => 'TYPE',
                    1 => 'LINK',
                    2 => 'BACKGROUND',
                ],
                'SET_BROWSER_TITLE' => 'N',
                'SET_LAST_MODIFIED' => 'N',
                'SET_META_DESCRIPTION' => 'N',
                'SET_META_KEYWORDS' => 'N',
                'SET_STATUS_404' => 'N',
                'SET_TITLE' => 'N',
                'SHOW_404' => 'N',
                'SORT_BY1' => 'SORT',
                'SORT_BY2' => 'SORT',
                'SORT_ORDER1' => 'ASC',
                'SORT_ORDER2' => 'ASC',
                'STRICT_SECTION_CHECK' => 'N',
                'COMPONENT_TEMPLATE' => 'main_links',
            ],
            false
        );
        ?>

        <div class="flowsBlock__side">
            <?php
            $APPLICATION->IncludeComponent(
                'natix:catalog.filter.price',
                '',
                [
                    'SECTION_CODE' => 'bukety-s-klubnikoy',
                ]
            );
            ?>
            <div class="flowsBlock__hot b-hot">
                <a class="b-hot__link b-hot__link_dark" href="/exclusive/">
                    <div class="b-hot__linkIcon">
                        <svg width="27" height="19" stroke="#182037" fill="#182037">
                            <use xlink:href="#crown"></use>
                        </svg>
                    </div>Эксклюзив
                </a>
                <a class="b-hot__link b-hot__link_red" href="/sales/">
                    <div class="b-hot__linkIcon">
                        <svg width="27" height="27" stroke="#D52A3F" fill="#D52A3F">
                            <use xlink:href="#star"></use>
                        </svg>
                    </div>Акции
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$APPLICATION->IncludeComponent(
    'natix:main.selection',
    '',
    [
        'BLOCK_ID' => 7, // блок "Букеты из клубники"
    ]
);
?>

<div class="whyUs">
    <div class="container whyUs__inner">
        <div class="whyUs__top">
            <h2 class="whyUs__title"><?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                    [
                        'AREA_FILE_SHOW' => 'file',
                        'PATH' => SITE_TEMPLATE_PATH . '/page_templates/main/why_us_h1.php',
                        'EDIT_TEMPLATE' => '',
                    ],
                    false
                );?></h2>
            <div class="whyUs__desc"><?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                    [
                        'AREA_FILE_SHOW' => 'file',
                        'PATH' => SITE_TEMPLATE_PATH . '/page_templates/main/why_us_description.php',
                        'EDIT_TEMPLATE' => '',
                    ],
                    false
                );?></div>
        </div>
        <div class="whyUsList">
            <?php $APPLICATION->IncludeComponent(
                'bitrix:news.list',
                'why_us',
                [
                    'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                    'ADD_SECTIONS_CHAIN' => 'N',
                    'AJAX_MODE' => 'N',
                    'AJAX_OPTION_ADDITIONAL' => '',
                    'AJAX_OPTION_HISTORY' => 'N',
                    'AJAX_OPTION_JUMP' => 'N',
                    'AJAX_OPTION_STYLE' => 'Y',
                    'CACHE_FILTER' => 'N',
                    'CACHE_GROUPS' => 'Y',
                    'CACHE_TIME' => '36000000',
                    'CACHE_TYPE' => 'A',
                    'CHECK_DATES' => 'Y',
                    'DETAIL_URL' => '',
                    'DISPLAY_BOTTOM_PAGER' => 'Y',
                    'DISPLAY_DATE' => 'N',
                    'DISPLAY_NAME' => 'Y',
                    'DISPLAY_PICTURE' => 'Y',
                    'DISPLAY_PREVIEW_TEXT' => 'N',
                    'DISPLAY_TOP_PAGER' => 'N',
                    'FIELD_CODE' => [
                        0 => 'ID',
                        1 => 'NAME',
                    ],
                    'FILTER_NAME' => '',
                    'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                    'IBLOCK_ID' => '8',
                    'IBLOCK_TYPE' => 'content',
                    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                    'INCLUDE_SUBSECTIONS' => 'N',
                    'MESSAGE_404' => '',
                    'NEWS_COUNT' => '20',
                    'PAGER_BASE_LINK_ENABLE' => 'N',
                    'PAGER_DESC_NUMBERING' => 'N',
                    'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                    'PAGER_SHOW_ALL' => 'N',
                    'PAGER_SHOW_ALWAYS' => 'N',
                    'PAGER_TEMPLATE' => '.default',
                    'PAGER_TITLE' => 'Новости',
                    'PARENT_SECTION' => '',
                    'PARENT_SECTION_CODE' => '',
                    'PREVIEW_TRUNCATE_LEN' => '',
                    'PROPERTY_CODE' => [
                        0 => 'BACKGROUND',
                        1 => 'SVG',
                    ],
                    'SET_BROWSER_TITLE' => 'N',
                    'SET_LAST_MODIFIED' => 'N',
                    'SET_META_DESCRIPTION' => 'N',
                    'SET_META_KEYWORDS' => 'N',
                    'SET_STATUS_404' => 'N',
                    'SET_TITLE' => 'N',
                    'SHOW_404' => 'N',
                    'SORT_BY1' => 'SORT',
                    'SORT_BY2' => 'SORT',
                    'SORT_ORDER1' => 'ASC',
                    'SORT_ORDER2' => 'ASC',
                    'STRICT_SECTION_CHECK' => 'N',
                    'COMPONENT_TEMPLATE' => 'why_us',
                ],
                false
            );?>

            <?php $APPLICATION->IncludeComponent(
                'bitrix:news.list',
                'main_social',
                [
                    'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                    'ADD_SECTIONS_CHAIN' => 'N',
                    'AJAX_MODE' => 'N',
                    'AJAX_OPTION_ADDITIONAL' => '',
                    'AJAX_OPTION_HISTORY' => 'N',
                    'AJAX_OPTION_JUMP' => 'N',
                    'AJAX_OPTION_STYLE' => 'Y',
                    'CACHE_FILTER' => 'N',
                    'CACHE_GROUPS' => 'Y',
                    'CACHE_TIME' => '36000000',
                    'CACHE_TYPE' => 'A',
                    'CHECK_DATES' => 'Y',
                    'DETAIL_URL' => '',
                    'DISPLAY_BOTTOM_PAGER' => 'Y',
                    'DISPLAY_DATE' => 'N',
                    'DISPLAY_NAME' => 'Y',
                    'DISPLAY_PICTURE' => 'Y',
                    'DISPLAY_PREVIEW_TEXT' => 'N',
                    'DISPLAY_TOP_PAGER' => 'N',
                    'FIELD_CODE' => [],
                    'FILTER_NAME' => '',
                    'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                    'IBLOCK_ID' => '13',
                    'IBLOCK_TYPE' => 'content',
                    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                    'INCLUDE_SUBSECTIONS' => 'N',
                    'MESSAGE_404' => '',
                    'NEWS_COUNT' => '20',
                    'PAGER_BASE_LINK_ENABLE' => 'N',
                    'PAGER_DESC_NUMBERING' => 'N',
                    'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                    'PAGER_SHOW_ALL' => 'N',
                    'PAGER_SHOW_ALWAYS' => 'N',
                    'PAGER_TEMPLATE' => '.default',
                    'PAGER_TITLE' => 'Новости',
                    'PARENT_SECTION' => '',
                    'PARENT_SECTION_CODE' => '',
                    'PREVIEW_TRUNCATE_LEN' => '',
                    'PROPERTY_CODE' => [],
                    'SET_BROWSER_TITLE' => 'N',
                    'SET_LAST_MODIFIED' => 'N',
                    'SET_META_DESCRIPTION' => 'N',
                    'SET_META_KEYWORDS' => 'N',
                    'SET_STATUS_404' => 'N',
                    'SET_TITLE' => 'N',
                    'SHOW_404' => 'N',
                    'SORT_BY1' => 'SORT',
                    'SORT_BY2' => 'SORT',
                    'SORT_ORDER1' => 'ASC',
                    'SORT_ORDER2' => 'ASC',
                    'STRICT_SECTION_CHECK' => 'N',
                    'COMPONENT_TEMPLATE' => 'main_social',
                ],
                false
            );?>
            <?php /*
            <div class="whyUsList__item">
                <div class="whyUs__item whyUs__item_more">
                    <div class="whyUs__itemInner">
                        <svg width="32" height="32" fill="#D52A3F"><use xlink:href="#star"></use></svg>
                        <div class="whyUs__itemTitle">Скидка — пригласи&nbsp;друга</div>
                        <a class="next-link whyUs__itemNextLink" href="#">
                            <span>Подробнее</span>
                            <svg><use xlink:href="#arr-right-light"></use></svg>
                        </a>
                    </div>
                </div>
            </div>
            */?>
        </div>
    </div>
</div>

<?php
$APPLICATION->IncludeComponent(
    'natix:main.selection',
    '',
    [
        'BLOCK_ID' => 8, // Букеты по любому поводу
    ]
);
?>

<?php
$APPLICATION->IncludeComponent(
    'natix:main.video.banner',
    '',
    [
        'BLOCK_ID' => 24, // Баннер с видео на главной
    ]
);
?>

<?php
$APPLICATION->IncludeComponent(
    'natix:main.selection',
    '',
    [
        'BLOCK_ID' => 9, // Дарите любимым подарки
    ]
);
?>

<?php $APPLICATION->IncludeComponent(
    'bitrix:news.list',
    'happy_clients',
    [
        'ACTIVE_DATE_FORMAT' => 'd.m.Y',
        'ADD_SECTIONS_CHAIN' => 'N',
        'AJAX_MODE' => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_STYLE' => 'Y',
        'CACHE_FILTER' => 'N',
        'CACHE_GROUPS' => 'Y',
        'CACHE_TIME' => '36000000',
        'CACHE_TYPE' => 'A',
        'CHECK_DATES' => 'Y',
        'DETAIL_URL' => '',
        'DISPLAY_BOTTOM_PAGER' => 'Y',
        'DISPLAY_DATE' => 'N',
        'DISPLAY_NAME' => 'Y',
        'DISPLAY_PICTURE' => 'Y',
        'DISPLAY_PREVIEW_TEXT' => 'N',
        'DISPLAY_TOP_PAGER' => 'N',
        'FIELD_CODE' => [
            0 => 'ID',
            1 => 'NAME',
        ],
        'FILTER_NAME' => '',
        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
        'IBLOCK_ID' => '14',
        'IBLOCK_TYPE' => 'content',
        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'INCLUDE_SUBSECTIONS' => 'N',
        'MESSAGE_404' => '',
        'NEWS_COUNT' => '20',
        'PAGER_BASE_LINK_ENABLE' => 'N',
        'PAGER_DESC_NUMBERING' => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
        'PAGER_SHOW_ALL' => 'N',
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_TEMPLATE' => '.default',
        'PAGER_TITLE' => 'Новости',
        'PARENT_SECTION' => '',
        'PARENT_SECTION_CODE' => '',
        'PREVIEW_TRUNCATE_LEN' => '',
        'PROPERTY_CODE' => [],
        'SET_BROWSER_TITLE' => 'N',
        'SET_LAST_MODIFIED' => 'N',
        'SET_META_DESCRIPTION' => 'N',
        'SET_META_KEYWORDS' => 'N',
        'SET_STATUS_404' => 'N',
        'SET_TITLE' => 'N',
        'SHOW_404' => 'N',
        'SORT_BY1' => 'SORT',
        'SORT_BY2' => 'SORT',
        'SORT_ORDER1' => 'ASC',
        'SORT_ORDER2' => 'ASC',
        'STRICT_SECTION_CHECK' => 'N',
        'COMPONENT_TEMPLATE' => 'happy_clients',
    ],
    false
);?>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
