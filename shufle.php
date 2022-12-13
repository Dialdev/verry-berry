<?
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule('iblock');

$quantity = 0;
$arExternl_ID = array();

$arSelect = Array("IBLOCK_ID", "ID", "SORT");
$arFilter = Array("IBLOCK_ID"=>5); // Здесь надо поставить ваш ID инфоблока.
$res = CIBlockElement::GetList(Array("ID"=>"ASC"), $arFilter, false, false, $arSelect);
while($ob = $res->Fetch())
    {
        $quantity++;
        $arExternal_ID[] = $ob;  		
    }
$new_sort = range(1, $quantity + 3);
shuffle($new_sort);
$el = new CIBlockElement;
foreach($arExternal_ID as $key=>$elem){
$arLoadProductArray = Array(
  "SORT"=> $new_sort[$key], // элемент изменен текущим пользователем
  );

$result = $el->Update($elem["ID"], $arLoadProductArray);
}

echo '<br><br>Всего элементов в каталоге - '.$quantity;
echo "<br><br> товары успешно перемешаны!";