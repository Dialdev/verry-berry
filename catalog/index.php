<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle("Каталог товаров");
?>

<div class="container">
    <h1><?php $APPLICATION->ShowTitle(false); ?></h1>
    <div class="catalogTop">
        <div class="catalogTopHeader">
            <?php
            $APPLICATION->IncludeComponent(
                'natix:catalog.filter.size',
                '',
                []
            );
            ?>
        </div>
    </div>
    <div class="catalogWrapper">
        <div class="catalogWrapper__side">
            <?php
            $APPLICATION->IncludeComponent(
                'natix:catalog.filter.price',
                '',
                []
            );
            ?>
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

            $APPLICATION->IncludeComponent(
	"natix:catalog.list", 
	".default", 
	$arParams = array(
		"FILTER" => $arrFilter,
		"ELEMENT_PER_PAGE" => "9",
		"SORT_FIELD" => 'sort',
		"SORT_ORDER" => 'asc',
		"USE_AJAX" => "Y",
		"AJAX_TEMPLATE_PAGE" => "ajax",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);
            ?>
        </div>
    </div>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
