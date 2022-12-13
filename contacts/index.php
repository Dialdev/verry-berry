<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetPageProperty("description", "Контактная информация Very Berry. Телефоны и адреса офисов в Москве и Санкт-Петербурге.");
$APPLICATION->SetTitle("Контакты");
?>

<?php
$APPLICATION->IncludeComponent("natix:shop.detail", "contacts1", Array(
	
	),
	false
);
?>

<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
