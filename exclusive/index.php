<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
?>

<div class="container">
    <h1><?php $APPLICATION->ShowTitle(); ?></h1>
    <div class="catalogWrapper">
        <div class="catalogWrapper__side">
            <div data-move="catalog-b-hot-get">
                <div class="b-hot b-hot_catalog">
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                        [
                            'AREA_FILE_SHOW' => 'file',
                            'PATH' => SITE_TEMPLATE_PATH . '/page_templates/exclusive_link.php',
                            'EDIT_TEMPLATE' => '',
                        ],
                        false
                    );?>
                    <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                        [
                            'AREA_FILE_SHOW' => 'file',
                            'PATH' => SITE_TEMPLATE_PATH . '/page_templates/sales_link.php',
                            'EDIT_TEMPLATE' => '',
                        ],
                        false
                    );?>
                </div>
            </div>
            <?php $APPLICATION->IncludeFile('/local/include/catalog_banners.php'); ?>
            <?php $APPLICATION->IncludeFile('/local/include/catalog_suggestions.php'); ?>
        </div>
            <?php
            global $arrFilter;
            $arrFilter['=PROPERTY_EXCLUSIVE'] = 1;
            
            $APPLICATION->IncludeComponent(
                'natix:catalog.list',
                '',
                [
                    'FILTER' => $arrFilter,
                    'ELEMENT_PER_PAGE' => 6,
                    'SORT_FIELD' => $_SESSION['SORT_FIELD'],
                    'SORT_ORDER' => $_SESSION['SORT_ORDER'],
                    'USE_AJAX' => 'Y',
                    'AJAX_TEMPLATE_PAGE' => 'ajax',
                ]
            );
            ?>
        </div>
    </div>
</div>

<?php
$APPLICATION->SetTitle('Эксклюзив');

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
