<?

$name  = strip_tags($_POST[name]);
$phone  = strip_tags($_POST[phone]);
$email  = strip_tags($_POST[email]);
$step  = strip_tags($_POST[step]);


if($phone =="Igor-kishko@mail.ru") {  exit();  }

$mytexts ='
Шаг №'.$step.' в заказе 

Имя '.$name.'
Телефон '.$phone.'
Почта '.$email.'

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
         array('text'=>'✅ Принят','callback_data'=>'{"action":"joy","count":0,"text":"✅"}'),
         array('text'=>'❌ Отменён','callback_data'=>'{"action":"hushed","count":0,"text":"❌"}'),
         array('text'=>'⁉️ Нет ответа','callback_data'=>'{"action":"cry","count":0,"text":"⁉️"}'),
      )
   );


$data = [
    'chat_id' => '-1001132125766', // название канала
    'text' => $mytexts,
    'reply_markup' => json_encode(array('inline_keyboard' => $keyboard)),

];

$resp = file_get_contents("https://api.telegram.org/bot{$botApiToken}/sendMessage?" . http_build_query($data) );
$takeinfos = json_decode($resp,true);

if($takeinfos["ok"] ==1) {
echo ' успешно ';
} else {
print_r($takeinfos);
}
?>


