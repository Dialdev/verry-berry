<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetPageProperty("description", "Адреса точек самовывоза в Санкт-Петербурге и Москве.");
$APPLICATION->SetTitle("Самовывоз");
?>

<?php
$APPLICATION->IncludeComponent("acro:shop.detail", "pickup1", Array(
	
	),
	false
);
?>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
