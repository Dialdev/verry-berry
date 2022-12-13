<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Ошибка доступа к странице 404');
\CHTTP::SetStatus('404 Not Found');
@define('ERROR_404', 'Y');
?>

<div class="container">
    <div class="b-404"><img class="b-404__img" src="<?php echo SITE_TEMPLATE_PATH; ?>/img/404.svg " alt="" role="presentation" />
        <div class="b-404__content">
            <div>Такой страницы не существует</div>
            <div class="b-404__text">Что-то пошло не так :(</div>
            <div class="button button-dark">НА ГЛАВНУЮ</div>
        </div>
    </div>
</div>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
