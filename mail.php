<?php

$e_mail = "no-reply@info.ru";

$subject = "Tema";

$body = "text";

$headers = 'From:'.$e_mail . "\r\n" .
    'Reply-To:'.$e_mail . "\r\n" .
    'X-Mailer: PHP/'. "\r\n" .
    "Content-type:text/plain". "\r\n";
	
$to = 'nick.sky.pc@gmail.com';

if ( mail($to, $subject, $body, $headers) === false )
    exit('Error');

echo "Success to $to. Date: ".date ('d.m.Y H:i:s');