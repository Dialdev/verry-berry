<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Мои заказы');
$_REQUEST['show_all']='Y';?>
    <div class="container">
        <div class="s-lk">
            <?php $APPLICATION->IncludeComponent('bitrix:main.include', '',
                [
                    'AREA_FILE_SHOW' => 'file',
                    'PATH' => SITE_TEMPLATE_PATH . '/page_templates/personal/sidebar.php',
                    'EDIT_TEMPLATE' => ''
                ],
                false
            ); ?>
            <div class="s-lk__content">
                <div class="lk-title hideme"><?$APPLICATION->ShowTitle()?></div>
                <?$APPLICATION->IncludeComponent(
                    'natix:order.list',
                    '',
                    Array(
                        'CACHE_TIME' => '0',
                        'CACHE_TYPE' => 'N'
                    )
                );?>

            </div>
        </div>
    </div>

<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
