<?
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("fileman");

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

if (check_bitrix_sessid() and $GLOBALS['USER']->IsAdmin())
{
	CHTMLEditor::RequestAction($action);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>