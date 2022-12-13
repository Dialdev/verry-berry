<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Бонусы');
?>
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
                <?$APPLICATION->IncludeComponent(
                    'natix:bonus',
                    '',
                   []
                );?>
            </div>
        </div>
    </div>

<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
