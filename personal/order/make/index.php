<?php
/**
 * @noinspection PhpIncludeInspection
 * @var \CMain $APPLICATION
 */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Оформление заказа');

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

<script>
$(function(){

$(document).on("click","#cart-order-1 button.button-submit",function(){

var name  = $("#cart-1-checkbox-name").val();
var phone  = $("#cart-1-checkbox-email").val();
var email  = $("#cart-1-checkbox-tel").val();


if(name.length>1 && phone.length>1 && email.length>1) {

$.post ("/local/api/order.php",{name: name,phone: phone,email: email, step:1}, function(data) {


});

} else {

}



});

});
</script>

<?
$APPLICATION->IncludeComponent('natix:sale.order.router', ''); ?>

<?php
/** Хрен его знает где искать шаблон для страницы make,
 * а нужно в уведомлении о доставке изменить текст.
 * Понять не могу к чему это выпад с типо Vue или Node js
 **/
?>
<script>
    setInterval(function () {
        $(`.order-grid .quest__content`).html('Рассчитывается исходя из выбранного интервала доставки и кол-ва км от МКАД/КАД, если таковые имеются');
    }, 500);
</script>
<?require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
