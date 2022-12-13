<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Post Mail");
?>
<?
if($_POST['email']){
	$email = htmlspecialchars($_POST['email']);
	$subject = "Test mail from russiandoc.ru";
	$headers = "From: Russian Doctor <info@russiandoc.ru>";
	$text = "It`s a test massage";
	$isMail = mail($email, $subject, $text, $headers, '-finfo@russiandoc.ru');
	if($isMail){
	?>
		<div style="color: green;">Mail send ok</div>
	<?
	} else {
	?>
		<div style="color: red;">Mail does not sent</div>
	<?
	}
}
?>
<form action="" method="post">
	<div>E-mail: <input type="text" size="80" name="email" />
	<input type="submit" value="Отправить" /></div>
</form>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>