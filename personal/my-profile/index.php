<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle("Личные данные");
global $USER;
?>
<div class="container">
    <h1 class="main-title"><?= $APPLICATION->ShowTitle(); ?></h1>
    <div class="s-lk">
        <?php $APPLICATION->IncludeComponent('bitrix:main.include', '',
            [
                'AREA_FILE_SHOW' => 'file',
                'PATH' => SITE_TEMPLATE_PATH . '/page_templates/personal/sidebar.php',
                'EDIT_TEMPLATE' => ''
            ],
            false
        ); ?>
        <?
            $APPLICATION->IncludeComponent(
                "natix:personal.settings",
                "",
                []
            );
        ?>
    </div>
</div>
<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
?>
