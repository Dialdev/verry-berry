<?



$mytexts ='
Тест письмо

';

 
/*
@BotFather.
Отправьте боту команду /newbot
логин + пароль
узнать id канала @ShowJsonBot
*/

$botApiToken = '1436846474:AAFh7XSR6klltpK4mtGSSJy_6zMUX2Hn1_A'; // токен бота

   $keyboard = array(
      array(
         array('text'=>'✅ Принят','callback_data'=>'{"action":"joy","count":0,"text":"taken"}'),
         array('text'=>'❌ Отменён','callback_data'=>'{"action":"hushed","count":0,"text":":hushed:"}'),
         array('text'=>'📵 Нет ответа','callback_data'=>'{"action":"cry","count":0,"text":":cry:"}'),
//         array('text'=>':rage:','callback_data'=>'{"action":"rage","count":0,"text":":rage:"}')
      )
   );


$data = [
    'chat_id' => '-1001132125766', // название канала
    'text' => $mytexts,
//    'reply_markup' => json_encode(array('inline_keyboard' => $keyboard)),
   'media' => json_encode([
        ['type' => 'photo', 'media' => 'attach://file1.png' ],
        ['type' => 'photo', 'media' => 'attach://file2.png' ],
    ]),

];


//$resp = file_get_contents("https://api.telegram.org/bot{$botApiToken}/sendMessage?" . http_build_query($data) );
//$takeinfos = json_decode($resp,true);

if($takeinfos["ok"] ==1) {
echo ' успешно ';
} else {
print_r($takeinfos);
}

$mpics[] = 'https://www.veryberrylab.ru/upload/iblock/e57/e5751106e61459b565977d8a6cdc7f51.JPG';
$mpics[] = 'https://www.veryberrylab.ru/upload/iblock/e57/e5751106e61459b565977d8a6cdc7f51.JPG';
$mpics[] = 'https://www.veryberrylab.ru/upload/iblock/e57/e5751106e61459b565977d8a6cdc7f51.JPG';

foreach($mpics as $mpics2) {

$mpics3[] = array("type"=>"photo","media" =>$mpics2);

}

    $ch = curl_init();
$data = [
    'chat_id' => '-1001132125766', // название канала
    'text' => $mytexts,
//    'reply_markup' => json_encode(array('inline_keyboard' => $keyboard)),
   'media' => json_encode($mpics3),

];
$url = 'https://api.telegram.org/bot'.$botApiToken.'/sendMediaGroup?';

    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

   $output = curl_exec($ch);
print_r( $output);
?>


