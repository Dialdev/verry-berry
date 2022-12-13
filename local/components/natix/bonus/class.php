<?php

use Bitrix\Main\Config\Option;
use Natix\Data\Bitrix\UserContainerInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BonusInfo extends CBitrixComponent
{
    /** @var UserContainerInterface */
    private $userContainer;
    
    public function __construct($component = null)
    {
        parent::__construct($component);
        $this->userContainer = \Natix::$container->get(UserContainerInterface::class);
    }

    private function getBonus()
    {
        $ar = CSaleUserAccount::GetByUserID($GLOBALS['USER']->getID(), 'RUB');
        $this->arResult['BONUS'] = 0;
        if ($ar) {
            $this->arResult['BONUS'] = (float)$ar['CURRENT_BUDGET'];
        }
        $this->arResult['PARTNER_LINK'] = sprintf(
            '%s://%s/?partnerId=%s',
            \Natix\Helpers\EnvironmentHelper::getParam('siteScheme'),
            \Natix\Helpers\EnvironmentHelper::getParam('siteHost'),
            $this->userContainer->getId()
        );
        $this->arResult['PARTNER_COMISSION'] = (int)Option::get('natix.settings', 'partner_comission', 300);
    }
    
    private function getPropertyUser()
    {
        $rsUsers = CUser::GetList(($by = "NAME"), ($order = "desc"), ['ID' => $GLOBALS['USER']->getID()], ['SELECT' => ['UF_LINK', 'UF_PROMOCODE'] ]);
        if ($arUser = $rsUsers->Fetch()) {
            $this->arResult['INFO'] = $arUser;
        }
    }
    
    public function executeComponent()
    {
        if ($GLOBALS['USER']->IsAuthorized()) {
            $this->getBonus();
            $this->getPropertyUser();
            $this->includeComponentTemplate();
        } else {
            LocalRedirect("/?open_auth=Y");
        }
    }
}
