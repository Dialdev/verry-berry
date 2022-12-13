<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetPageProperty("description", "Отвечаем на ваши частые вопросы о букетах, оплате и доставке.");
$APPLICATION->SetTitle("Часто задаваемые вопросы");
?>
<div class='container'>
    <h1><? $APPLICATION->ShowTitle() ?></h1>
    <div class='faqWrapper'>
        <div class='faqWrapper__content'>
            <div class='faq'>
                <?php $APPLICATION->IncludeComponent('bitrix:news.list', 'faq',
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
                        'DISPLAY_BOTTOM_PAGER' => 'N',
                        'DISPLAY_DATE' => 'N',
                        'DISPLAY_NAME' => 'N',
                        'DISPLAY_PICTURE' => 'N',
                        'DISPLAY_PREVIEW_TEXT' => 'N',
                        'DISPLAY_TOP_PAGER' => 'N',
                        'FIELD_CODE' => array('', ''),
                        'FILTER_NAME' => '',
                        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                        'IBLOCK_ID' => '12',
                        'IBLOCK_TYPE' => 'content',
                        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                        'INCLUDE_SUBSECTIONS' => 'Y',
                        'MESSAGE_404' => '',
                        'NEWS_COUNT' => '9999',
                        'PAGER_BASE_LINK_ENABLE' => 'N',
                        'PAGER_DESC_NUMBERING' => 'N',
                        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                        'PAGER_SHOW_ALL' => 'N',
                        'PAGER_SHOW_ALWAYS' => 'N',
                        'PAGER_TEMPLATE' => '.default',
                        'PAGER_TITLE' => 'Часто задаваемые вопросы',
                        'PARENT_SECTION' => '',
                        'PARENT_SECTION_CODE' => '',
                        'PREVIEW_TRUNCATE_LEN' => '',
                        'PROPERTY_CODE' => array('', ''),
                        'SET_BROWSER_TITLE' => 'N',
                        'SET_LAST_MODIFIED' => 'N',
                        'SET_META_DESCRIPTION' => 'N',
                        'SET_META_KEYWORDS' => 'N',
                        'SET_STATUS_404' => 'N',
                        'SET_TITLE' => 'N',
                        'SHOW_404' => 'N',
                        'SORT_BY1' => 'SORT',
                        'SORT_BY2' => 'ID',
                        'SORT_ORDER1' => 'ASC',
                        'SORT_ORDER2' => 'ASC',
                        'STRICT_SECTION_CHECK' => 'N'
                    ]
                ); ?>
            </div>
        </div>
        <div class='faqWrapper__side'>
            <div class="page-widget">
                <div class="page-widget__header">
                    <div class="page-widget__title">
                        <?php $APPLICATION->IncludeComponent('bitrix:main.include', '',
                            [
                                'AREA_FILE_SHOW' => 'file',
                                'PATH' => SITE_TEMPLATE_PATH . '/page_templates/faq/title.php',
                                'EDIT_TEMPLATE' => ''
                            ],
                            false
                        ); ?>
                    </div>
                    <div class="page-widget__subtitle">
                        <?php $APPLICATION->IncludeComponent('bitrix:main.include', '',
                            [
                                'AREA_FILE_SHOW' => 'file',
                                'PATH' => SITE_TEMPLATE_PATH . '/page_templates/faq/sub_title.php',
                                'EDIT_TEMPLATE' => ''
                            ],
                            false
                        ); ?>
                    </div>
                </div>
                <div class="b-contact b-contact_widget">
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include', '',
                        [
                            'AREA_FILE_SHOW' => 'file',
                            'PATH' => SITE_TEMPLATE_PATH . '/page_templates/faq/phone.php',
                            'EDIT_TEMPLATE' => ''
                        ],
                        false
                    ); ?>
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include', '',
                        [
                            'AREA_FILE_SHOW' => 'file',
                            'PATH' => SITE_TEMPLATE_PATH . '/page_templates/faq/email.php',
                            'EDIT_TEMPLATE' => ''
                        ],
                        false
                    ); ?>
                </div>

                <?php $APPLICATION->IncludeComponent('bitrix:news.list', 'soc',
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
                        'DISPLAY_BOTTOM_PAGER' => 'N',
                        'DISPLAY_DATE' => 'N',
                        'DISPLAY_NAME' => 'N',
                        'DISPLAY_PICTURE' => 'N',
                        'DISPLAY_PREVIEW_TEXT' => 'N',
                        'DISPLAY_TOP_PAGER' => 'N',
                        'FIELD_CODE' => array('', ''),
                        'FILTER_NAME' => '',
                        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                        'IBLOCK_ID' => '13',
                        'IBLOCK_TYPE' => 'content',
                        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                        'INCLUDE_SUBSECTIONS' => 'Y',
                        'MESSAGE_404' => '',
                        'NEWS_COUNT' => '9999',
                        'PAGER_BASE_LINK_ENABLE' => 'N',
                        'PAGER_DESC_NUMBERING' => 'N',
                        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                        'PAGER_SHOW_ALL' => 'N',
                        'PAGER_SHOW_ALWAYS' => 'N',
                        'PAGER_TEMPLATE' => '.default',
                        'PAGER_TITLE' => 'Cоциальные сети',
                        'PARENT_SECTION' => '',
                        'PARENT_SECTION_CODE' => '',
                        'PREVIEW_TRUNCATE_LEN' => '',
                        'PROPERTY_CODE' => array('', ''),
                        'SET_BROWSER_TITLE' => 'N',
                        'SET_LAST_MODIFIED' => 'N',
                        'SET_META_DESCRIPTION' => 'N',
                        'SET_META_KEYWORDS' => 'N',
                        'SET_STATUS_404' => 'N',
                        'SET_TITLE' => 'N',
                        'SHOW_404' => 'N',
                        'SORT_BY1' => 'SORT',
                        'SORT_BY2' => 'ID',
                        'SORT_ORDER1' => 'ASC',
                        'SORT_ORDER2' => 'ASC',
                        'STRICT_SECTION_CHECK' => 'N'
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>
<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>
