<?
$MODULE_ID = "cloudpayments.cloudpayment";
$updater->CopyFiles("install/php_interface", "php_interface",true,true);
$updater->CopyFiles("install/admin", "modules/cloudpayments.cloudpayment/admin",true,true);
$updater->CopyFiles("install/main", "modules/cloudpayments.cloudpayment/",true,true);
$updater->CopyFiles("install/front", "cloudPayments",true,true);
$updater->CopyFiles("install/images", "images/sale/sale_payments",true,true);
?>