<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$res = \Bitrix\Main\Mail\Event::send(array(
    "EVENT_NAME" => "SALE_NEW_ORDER",
    "LID" => "s1",
    "C_FIELDS" => array(
        "EMAIL" => "stepanov.aleksey@bk.ru",
        "USER_ID" => 2
    ),
));

print_r($res);