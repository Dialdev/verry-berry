<?php

include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

try
{
    if (!isset($_REQUEST["Amount"]) || empty($_REQUEST["Amount"])) throw new \Exception("Amount not found");
    if (!isset($_REQUEST["InvoiceId"]) || empty($_REQUEST["InvoiceId"])) throw new \Exception("InvoiceId not found");
    if (!isset($_POST["AuthCode"]) || empty($_POST["AuthCode"])) throw new \Exception("AuthCode not found");
    if (!isset($_POST["Token"]) || empty($_POST["Token"])) throw new \Exception("Token not found");

    \Bitrix\Main\Loader::includeModule("sale");

    $doubleAmount = floatval(htmlspecialchars($_REQUEST["Amount"]));
    $intOrderID   = intval(htmlspecialchars($_REQUEST["InvoiceId"]));

    $objOrder = \Bitrix\Sale\Order::load($intOrderID);
    if (empty($objOrder)) throw new \Exception("order {$intOrderID} not found");
    if ($doubleAmount != $objOrder->getField("PRICE")) throw new \Exception("price is incorrect");

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





