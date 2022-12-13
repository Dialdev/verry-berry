<?php
/**
 * @var array $arParams
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
global $USER;
foreach ($arResult as &$item) {
    switch ($item['PARAMS']['type']) {
        case 'bonus':
            $ar = CSaleUserAccount::GetByUserID($USER->getID(), 'RUB');
            if ($ar) {
                $item['COUNT'] = floatval($ar['CURRENT_BUDGET']);
            }
            break;
    }
}
unset($item);
