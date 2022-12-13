<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карта сайта");
?>
<div class="container">
<h1>Карта сайта</h1>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.map", 
	".default", 
	array(
		"LEVEL" => "5",
		"COL_NUM" => "2",
		"SHOW_DESCRIPTION" => "N",
		"SET_TITLE" => "Y",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>