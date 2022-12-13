<?php

   define( 'TOKEN', '1447433426:AAHyohZC-cJd_VHIdB9Px2QgjEKL9O-BSu0' );
   define( 'CHAT_ID', '-1001203757730' ); // @name_chat
   define( 'API_URL', 'https://api.telegram.org/bot' . TOKEN . '/' );

   function request($method, $params = array()) {
      if ( !empty($params) ) {
		    $url = API_URL . $method . "?" . http_build_query($params);
      } else {
         $url = API_URL . $method;
      }

      return json_decode(file_get_contents($url), JSON_OBJECT_AS_ARRAY);
   }

   function editMessageReplyMarkup($params){
      //В этом цикле мы изменяем сами кнопки, а именно текст кнопки и значение параметра callback_data
      foreach ( $params['inline_keyboard'][0] as $key => $value ) {
         $data_for = json_decode($value->callback_data, true); // изначально у нас callback_data храниться в виде json-а, декатируем в массив
         if ( $params['data']['action'] == $data_for['action'] ) { // определяем, на какую именно кнопку нажал пользователь под сообщением
            $data_for['count']++; //плюсуем единичку


if($data_for['text'] =="taken") {
//$stext = json_encode("Привет");
//$data_for['text'] = $stext;
}

            $value->text = $data_for['text'] . " " . $data_for['count']; // Изменяем текст кнопки смайлик + количество лайков
         }
         $value->callback_data = json_encode($data_for); // callback_data кнопки кодируем в json
         $params['inline_keyboard'][0][$key] = (array)$value; // изменяем кнопку на новую
      }

      //Изменяем кнопки к сообщению
      request("editMessageReplyMarkup", array(
         'chat_id' => CHAT_ID,
         'message_id' => $params['message_id'],
         'reply_markup' => json_encode(array('inline_keyboard' => $params['inline_keyboard'])),
      ));

      //Выводим сообщение в чат
      request("answerCallbackQuery", array(
         'callback_query_id' => $params['callback_query_id'],
         'text' => "Спасибо! Вы поставили " . $params['data']['text'],
      ));
}

   $result = json_decode(file_get_contents('php://input')); // получаем результат нажатия кнопки

   $inline_keyboard = $result->callback_query->message->reply_markup->inline_keyboard; // текущее состояние кнопок при нажатии на одну из 5 кнопок
   $data = json_decode($result->callback_query->data, true); // получаем значение с кнопки, а именно с параметра callback_data нажатой кнопки
   $message_id = $result->callback_query->message->message_id; // ID сообщения в чате
   $callback_query_id = $result->callback_query->id; //ID полученного результата
   $user_id = $result->callback_query->from->id; // ID пользователя

		  //$db_message = $db->super_query("SELECT * FROM bot_like WHERE message_id={$message_id}"); //Ищем в БД ID сообщения

   /*
   Я использую библиотеку ($db = new db;) от CMS DLE для работы с БД.
   super_query - этот метод возвращает первую найденную запись в виде массива
   */

   if ( $db_message === null ) {
      // Если не нашли в БД ID сообщения, записываем в БД текущий ID сообщения и ID пользователя, который отреагировал (нажал на одну из 5 кнопок) на сообщение. 
	   //$db->query("INSERT INTO " . PREFIX . "_posting_tg_bot_like (message_id, users) VALUES ('{$message_id}', '{$user_id}')");


//if( =="taken")

$log = date('Y-m-d H:i:s') . '  '. print_r($inline_keyboard, true);
file_put_contents($_SERVER["DOCUMENT_ROOT"]. '/local/api/debug.txt', $log . PHP_EOL, FILE_APPEND);

$log = date('Y-m-d H:i:s') . '  '. print_r($data, true);
file_put_contents($_SERVER["DOCUMENT_ROOT"]. '/local/api/debug.txt', $log . PHP_EOL, FILE_APPEND);


      editMessageReplyMarkup(array(
         'inline_keyboard' => $inline_keyboard,
         'data' => $data,
         'message_id' => $message_id,
         'callback_query_id' => $callback_query_id
      ));
   } else {
      //Если в БД нашли сообщение
      $users = explode(",", $db_message['users']);
      
      // Если нашли в БД пользователя, выводим сообщение "Вы уже нажали на одну из 5 кнопок"
      if( in_array($user_id, $users) ) {
         request("answerCallbackQuery", array(
            'callback_query_id' => $callback_query_id,
            'text' => "Вы уже отреагировали на новость",
         ));
      } 

      // Если не нашли ID в БД изменяем одну из 5 кнопок и добавляем ID пользователя
      else {
         editMessageReplyMarkup(array(
            'inline_keyboard' => $inline_keyboard,
            'data' => $data,
            'message_id' => $message_id,
            'callback_query_id' => $callback_query_id
         ));

         array_push($users, $user_id);
         $users = implode(',', $users);
		  //  $db->query("UPDATE bot_like SET users='{$users}' WHERE message_id='{$message_id}'");
    }
}

?>
