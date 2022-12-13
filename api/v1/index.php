<?php
/**
 * Точка входа для модуля API
 * @author Artem Luchnikov <artem@luchnikov.ru>
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @global CDatabase $DB
 */

define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC', 'Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('SITE_ID', 's1');

/** @noinspection PhpIncludeInspection */
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

global $APPLICATION;

$APPLICATION->RestartBuffer();

while (ob_get_level()) {
    ob_end_clean();
}

ob_end_clean();

/** @noinspection PhpIncludeInspection */
require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/module_slim_api.php';

/** @noinspection PhpIncludeInspection */
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
