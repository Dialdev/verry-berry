<?php

IncludeModuleLangFile(__FILE__);

class natix_settings extends CModule
{
    const MODULE_ID = 'natix.settings';
    public $MODULE_ID = 'natix.settings';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $strError = '';

    /** @noinspection PhpConstructorStyleInspection */
    public function natix_settings()
    {
        $arModuleVersion = array();
        $path = str_replace('\\', '/', __FILE__);
        $path = substr($path, 0, strlen($path) - strlen('/index.php'));
        include($path.'/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = GetMessage('NATIX_SETTINGS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('NATIX_SETTINGS_DESCRIPTION');
    }

    /**
     * @throws \Bitrix\Main\LoaderException
     */
    public function DoInstall()
    {
        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}
