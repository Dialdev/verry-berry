<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetPageProperty("description", "Подробные условия оплаты заказов ягодно-фруктовых букетов в Москве и Санкт-Петербурге.");
$APPLICATION->SetTitle("Оплата - способы оплаты заказов");
?>
<div class="container">
    <h1>Оплата</h1>
    <div class="b-payment">
        <?php $APPLICATION->IncludeComponent(
            'bitrix:news.list',
            'payments',
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
                'IBLOCK_ID' => '15',
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
                'COMPONENT_TEMPLATE' => 'payments',
            ],
            false
        );?>
        <div class="b-payment__item">
            <div class="b-payment__title">
                <svg width="20" height="26">
                    <use xlink:href="#lock"></use>
                </svg>
                <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                    [
                        'AREA_FILE_SHOW' => 'file',
                        'PATH' => SITE_TEMPLATE_PATH . '/page_templates/payment/payment_security_h1.php',
                        'EDIT_TEMPLATE' => ''
                    ],
                    false
                );?>
            </div>
            <div class="b-payment__text">
                <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                    [
                        'AREA_FILE_SHOW' => 'file',
                        'PATH' => SITE_TEMPLATE_PATH . '/page_templates/payment/payment_security_descr.php',
                        'EDIT_TEMPLATE' => ''
                    ],
                    false
                );?>
            </div>
        </div>
    </div>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');