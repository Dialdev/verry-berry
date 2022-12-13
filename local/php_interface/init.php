<?php

use Natix\Helpers\EnvironmentHelper;
use Natix\Service\Core\Bundle\BundleService;
use Zend\ServiceManager\ServiceManager;

if (file_exists(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}

if (file_exists(dirname(__DIR__, 3) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
}

include_once realpath(dirname(__DIR__, 3)) . '/config/config.php';

\Bex\Monolog\MonologAdapter::loadConfiguration();

$container = new \Natix\Natix\Container(
    EnvironmentHelper::getParam('service_manager')
);
\Natix::$container = $container;

/** @var BundleService $bundleService */
$bundleService = \Natix::$container->get(BundleService::class);
$bundleService->build();

$eventListener = new \Maximaster\Tools\Events\Listener();
$eventListener->addNamespace(
    'Natix\\EventHandlers',
    $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/EventHandlers'
);
$eventListener->register();

$eventManager = \Bitrix\Main\EventManager::getInstance();

function addCustomDeliveryServices()
{
    return new \Bitrix\Main\EventResult(
        \Bitrix\Main\EventResult::SUCCESS,
        [
            '\Natix\Service\Sale\Delivery\Handlers\CourierDeliveryHandler'
            => 'htdocs/local/php_interface/classes/Service/Sale/Delivery/Handlers/CourierDeliveryHandler.php',
            '\Natix\Service\Sale\Delivery\Handlers\PickupDeliveryHandler'
            => 'htdocs/local/php_interface/classes/Service/Sale/Delivery/Handlers/PickupDeliveryHandler.php',
        ]
    );
}

$eventManager->addEventHandler(
    'sale',
    'onSaleDeliveryHandlersClassNamesBuildList',
    'addCustomDeliveryServices'
);

$eventManager->addEventHandler(
    'sale',
    'OnSaleBeforeStatusOrder',
    ['Natix\Event\OrderEvents', 'OnSaleBeforeStatusOrder']
);

$eventManager->addEventHandler(
    'sale',
    'OnSaleStatusOrder',
    ['Natix\Event\OrderEvents', 'OnSaleStatusOrderHandler']
);

$eventManager->addEventHandler(
    'sale',
    'OnSaleBeforeOrderDelete',
    ['Natix\Event\OrderEvents', 'OnSaleBeforeOrderDelete']
);

AddEventHandler("main", "OnBeforeProlog", "CriticalVulnFix", -1);

function CriticalVulnFix()
{
    if (is_array($_FILES) && !empty($_FILES) && !$GLOBALS['USER']->IsAdmin()) {
        $GLOBALS['APPLICATION']->RestartBuffer();
        http_response_code(400);
        ;
    }
}

AddEventHandler("sale","OnOrderSave","My_OnOrderSave"); 
function My_OnOrderSave($ID, $arFields, $arOrder, $isNew)  {
   $ID = IntVal($ID);

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");
   
if($isNew) {

      $arProps = array();
      $res = CSaleOrderPropsValue::GetOrderProps($arOrder['ID']);
while ($prop = $res->Fetch()) {
          $arProps[$prop['CODE']] = $prop;
}

      $res = CSaleBasket::GetList(
         array('NAME' => 'ASC'),
         array('ORDER_ID' => $arOrder['ID']),
         false,
         false,
         array('ID', 'NAME', 'QUANTITY', 'PRICE', 'CURRENCY', 'DETAIL_PAGE_URL', 'PRODUCT_ID')
      );
      while ($el = $res->GetNext()) {

$strBasket[] = $el;
      }
$isgoods = 'Товары';
foreach($strBasket as $strBasket2) {
$isgoods .= '
'.$strBasket2[NAME].' кол-во '.$strBasket2[QUANTITY].' цена '.$strBasket2[PRICE];
$mcout[$strBasket2[PRODUCT_ID]] = $strBasket2[PRODUCT_ID];
}


$arSelect_s1 = Array( "PREVIEW_PICTURE");
$arFilter_s1 = Array("ID"=>$mcout, "ACTIVE"=>"Y");
$res_s1 = CIBlockElement::GetList(Array(), $arFilter_s1, false, Array("nPageSize"=>500), $arSelect_s1);
while($ob_s1 = $res_s1->GetNextElement())
{
  $arFields_s1 = $ob_s1->GetFields();
if($arFields_s1["PREVIEW_PICTURE"] >0) {
$mpics[] = 'https://www.veryberrylab.ru'.CFile::GetPath($arFields_s1["PREVIEW_PICTURE"]);
}
}


      if ($arOrder['DELIVERY_ID']) {
         $arDelivery = CSaleDelivery::GetByID($arOrder['DELIVERY_ID']);
}

      if ($arOrder['PAY_SYSTEM_ID']) {
         $arPaySystem = CSalePaySystem::GetByID($arOrder['PAY_SYSTEM_ID']);
      }

if (!($arOrderingo = CSaleOrder::GetByID($arOrder['ID'])))
{

}
else
{


 if ($arOrderingo['PAY_SYSTEM_ID']) {
         $arPaySystem = CSalePaySystem::GetByID($arOrderingo['PAY_SYSTEM_ID']);
      }
      if ($arOrderingo['DELIVERY_ID']) {
         $arDelivery = CSaleDelivery::GetByID($arOrderingo['DELIVERY_ID']);
      }

}

$tostotprice = number_format($arOrderingo['PRICE'],0,'','');

////////////////////

foreach($mpics as $mpics2) {

$mpics3[] = array("type"=>"photo","media" =>$mpics2);


}

if(!empty($arProps[DELIVERY_DATE][VALUE])) {
$isttochats_be[0] = ''.$arProps[DELIVERY_DATE][VALUE].' ';
}

if(!empty($arProps[EXACT_TIME][VALUE])) {
$isttochats_be[1] = ''.$arProps[EXACT_TIME][VALUE].' ';
}
else if(!empty($arProps[DELIVERY_INTERVAL][VALUE])) {
$isttochats_be[1] = ''.$arProps[DELIVERY_INTERVAL][VALUE].' ';
}

if(!empty($arProps[SEND_PHOTO][VALUE])) {
$isttochats_be[2] = 'Прислать фото до доставки на '.$arProps[SEND_PHOTO][VALUE];
}

if($arProps[NOT_CALL_CONFIRM][VALUE] == "Y") {  ///////// ОТКЛЮЧИТЬ ???
$isttochats_be[3] = 'Не звонить для подтверждения';
}

if(!empty($arProps[POSTCARD][VALUE])) {
$isttochats_be[4] = '
Текст открытки '.$arProps[POSTCARD][VALUE];
}

if(!empty($arOrderingo[USER_DESCRIPTION])) {
$isttochats_be[5] = '
Комментарий клиента '.$arOrderingo[USER_DESCRIPTION];
}

if(!empty($arProps[RECIPIENT_NAME][VALUE])) {
$isttochats_be[6] = '
Имя получателя '.$arProps[RECIPIENT_NAME][VALUE].' ';
}

if(!empty($arProps[RECIPIENT_PHONE][VALUE])) {
$isttochats_be[7] = '
Телефон '.$arProps[RECIPIENT_PHONE][VALUE].' ';
}


if(!empty($arProps[CITY][VALUE])) {
$isttochats2 = 'Город '.$arProps[CITY][VALUE].' ';
}

if(!empty($arProps[STREET][VALUE])) {
$isttochats2 .= ' Ул '.$arProps[STREET][VALUE].' ';
}

if(!empty($arProps[HOME][VALUE])) {
$isttochats2 .= ' Дом '.$arProps[HOME][VALUE].' ';
}

if(!empty($arProps[APARTMENT][VALUE])) {
$isttochats2 .= ' Кв '.$arProps[APARTMENT][VALUE].' ';
}

if(!empty($arProps[DELIVERY_COMMENT][VALUE])) {
$isttochats_be[8] = ' 
Комментарий к адресу: '.$arProps[DELIVERY_COMMENT][VALUE].' ';
}

$st = 0;
if($arProps[RECIPIENT_MAKE_PHOTO][VALUE] == "Y") {
$isttochats4 .= ' 
Сделать фото с получателем ';
$st++;
}

if($arProps[IS_SURPRISE][VALUE] == "Y") {
$isttochats4 .= ' 
Сюрприз, не звонить перед вручением ';
$st++;
}

if($arProps[ANONYMOUS_ORDER][VALUE] == "Y") {
$isttochats4 .= ' 
Анонимный заказ ';
$st++;
}

if($st >0 ) {
$isttochats_be[9] = '
Проставленные галочки  '.$isttochats4.'
';
}

$datasnews = '
Заказ №'.$arOrder['ID'].'
'.$isttochats_be[2].'
'.$isttochats_be[3].'
Дата:
'.$isttochats_be[0].' 
'.$isttochats_be[1].''.$isttochats_be[5].''.$isttochats_be[4].'
Имя заказчика:'.$arProps[NAME][VALUE].'
Почта: '.$arProps[EMAIL][VALUE].'
Телефон: '.$arProps[PERSONAL_PHONE][VALUE].' '.$isttochats_be[6].' '.$isttochats_be[7].'
'.$isttochats2.' 
'.$isttochats_be[8].'
Оплата: '.$arPaySystem['NAME'].'
Стоимость доставки: '.$arOrderingo[PRICE_DELIVERY].' ₽
Сумма: '.$tostotprice.' руб.
'.$isgoods;


/*
$log = date('Y-m-d H:i:s') . '  '. print_r($arProps, true);
file_put_contents($_SERVER["DOCUMENT_ROOT"]. '/local/api/debug2.txt', $log . PHP_EOL, FILE_APPEND);

$log = date('Y-m-d H:i:s') . '  '. print_r($arOrderingo, true);
file_put_contents($_SERVER["DOCUMENT_ROOT"]. '/local/api/debug2.txt', $log . PHP_EOL, FILE_APPEND);
*/


 





 
   $keyboard = array(
      array(
         array('text'=>'✅ Принят','callback_data'=>'{"action":"joy","count":0,"text":"taken"}'),
         array('text'=>'❌ Отменён','callback_data'=>'{"action":"hushed","count":0,"text":":hushed:"}'),
         array('text'=>'⁉️ Нет ответа','callback_data'=>'{"action":"cry","count":0,"text":":cry:"}'),
      )
   );


$botApiToken = '1447433426:AAHyohZC-cJd_VHIdB9Px2QgjEKL9O-BSu0'; // токен бота

$data = [
    'chat_id' => '-1001203757730', // название канала
    'text' => $datasnews,
    'reply_markup' => json_encode(array('inline_keyboard' => $keyboard)),

];

$resp = file_get_contents("https://api.telegram.org/bot{$botApiToken}/sendMessage?" . http_build_query($data) );
$takeinfos = json_decode($resp,true);

if($takeinfos["ok"] ==1) {
//echo ' успешно ';
} else {
//print_r($takeinfos);
}




//////////////////////////


////////////////////////////
    $ch = curl_init();
$data = [
    'chat_id' => '-1001203757730', // название канала
   'media' => json_encode($mpics3),
];
$url = 'https://api.telegram.org/bot'.$botApiToken.'/sendMediaGroup?';

    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

   $output = curl_exec($ch);


$log = date('Y-m-d H:i:s') . '  '. print_r($output, true);
file_put_contents($_SERVER["DOCUMENT_ROOT"]. '/local/api/debug2.txt', $log . PHP_EOL, FILE_APPEND);


$log = date('Y-m-d H:i:s') . '  '. print_r($mpics3, true);
file_put_contents($_SERVER["DOCUMENT_ROOT"]. '/local/api/debug2.txt', $log . PHP_EOL, FILE_APPEND);

$log = date('Y-m-d H:i:s') . '  '. print_r($mpics, true);
file_put_contents($_SERVER["DOCUMENT_ROOT"]. '/local/api/debug2.txt', $log . PHP_EOL, FILE_APPEND);


//////////////////////////////


}

}

/**
 * Отслеживание регистрации пользователя при оформлении заказа.
 * Отправление пользователю на почту данных для входа на сайт.
 * Регистрация пользователя из корзины производится в файле local/php_interface/classes/Module/Api/Service/User/UserService.php,
 * в методе addUser.
 */
$eventManager->addEventHandler('main', 'OnAfterUserAdd', function (&$arArgs) {
//    $strTypeRegistration = \Bitrix\Main\Config\Option::get("main","userRegistrationFromBasket","N");
//    if ($strTypeRegistration == "Y")
//    {
//        # Если регистрация пользователя произошла из корзины автоматически
//        \Bitrix\Main\Mail\Event::send([
//            "EVENT_NAME" => "NEW_USER",
//            "LID"        => "s1",
//            "MESSAGE_ID" => 56,
//            "C_FIELDS"   => array(
//                "USER_NAME"     => $arArgs["NAME"],
//                "EMAIL"         => $arArgs["EMAIL"],
//                "USER_LOGIN"    => $arArgs["LOGIN"],
//                "USER_PASSWORD" => $arArgs["UF_PASSWORD"]
//            )
//        ]);
//    }

    file_put_contents(__DIR__ . "/server.log", print_r($_SERVER, true));
    file_put_contents(__DIR__ . "/addUser.log", print_r($arArgs, true));
});


