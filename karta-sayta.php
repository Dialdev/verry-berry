<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("tags", "Карта сайта");
$APPLICATION->SetPageProperty("keywords", "Карта сайта");
$APPLICATION->SetPageProperty("description", "Карта сайта");
$APPLICATION->SetTitle("Карта сайта");
?>

<div class="container">
	<h1>Карта сайта</h1>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.map", 
	".default", 
	array(
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
		"COL_NUM" => "1",
		"LEVEL" => "5",
		"SET_TITLE" => "Y",
		"SHOW_DESCRIPTION" => "N",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>