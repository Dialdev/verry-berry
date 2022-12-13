<?php

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

try
{
    file_put_contents(__DIR__ . "/lastRequest.log", date("d.m.Y H:i:s "). print_r($_POST, true));

    if (!isset($_POST["Amount"]) || empty($_POST["Amount"])) throw new \Exception("Amount not found");
    if (!isset($_POST["InvoiceId"]) || empty($_POST["InvoiceId"])) throw new \Exception("InvoiceId not found");
    if (!isset($_POST["AuthCode"]) || empty($_POST["AuthCode"])) throw new \Exception("AuthCode not found");
    if (!isset($_POST["Token"]) || empty($_POST["Token"])) throw new \Exception("Token not found");
    if (!isset($_POST["Status"]) || empty($_POST["Status"])) throw new \Exception("status not found");

    if ($_POST["Status"] != "Completed") die();

    \Bitrix\Main\Loader::includeModule("sale");

    $doubleAmount = floatval(htmlspecialchars($_POST["Amount"]));
    $intOrderID   = intval(htmlspecialchars($_POST["InvoiceId"]));

    $objOrder = \Bitrix\Sale\Order::load($intOrderID);
    if (empty($objOrder)) throw new \Exception("order {$intOrderID} not found");

    $arPayments = $objOrder->getPaymentCollection();
    foreach ($arPayments as $objPayment)
    {
        if ($objPayment->isPaid()) continue;
        $objPayment->setPaid("Y");
    }

    $objOrder->setField("STATUS_ID", "P");
    $objOrder->save();
}
catch (Exception $e)
{
    file_put_contents(__DIR__ . "/success-errors.log", date("[d.m.Y H:i:s] - "). $e->getMessage(). "\n");
}





