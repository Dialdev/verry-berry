<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Карточка товара');
$APPLICATION->AddViewContent('body-css-class', 'page-product');
?>

<div class="container">
    <?php
    $APPLICATION->IncludeComponent(
        'natix:catalog.element.router',
        '',
        [
            'ACTIVE_OFFER_ID' => $_REQUEST['offer_id'],
        ]
    );
    ?>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
