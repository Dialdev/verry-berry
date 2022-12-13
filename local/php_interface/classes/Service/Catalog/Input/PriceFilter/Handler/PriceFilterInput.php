<?php

namespace Natix\Service\Catalog\Input\PriceFilter\Handler;

/**
 * Создаёт новый пользовательский тип свойства "Диапазоны фильтра по цене"
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PriceFilterInput extends \CUserTypeString
{
    /**
     * @return array
     */
    public function getUserTypeDescription()
    {
        return [
            'USER_TYPE_ID' => 'price_filter',
            'CLASS_NAME' => self::class,
            'DESCRIPTION' => 'Диапазоны фильтра по цене',
            'BASE_TYPE' => 'string',
        ];
    }

    /**
     * @param array $arUserField
     * @param array $arHtmlControl
     * @return string
     */
    public function GetEditFormHTML($arUserField, $arHtmlControl)
    {
        return self::getEditRowHtml($arUserField, $arHtmlControl);
    }

    /**
     * @param $arUserField
     * @param $arHtmlControl
     * @return string|string[]
     */
    public function getEditRowHtml($arUserField, $arHtmlControl)
    {
        if (
            $arUserField['VALUE'] === false
            && isset($arUserField['SETTINGS']['DEFAULT_VALUE'])
            && $arUserField['SETTINGS']['DEFAULT_VALUE'] !== ''
        ) {
            $arHtmlControl['VALUE'] = json_encode([
                'PRICE_FROM' => htmlspecialcharsbx($arUserField['SETTINGS']['DEFAULT_VALUE']),
                'PRICE_TO' => '',
            ]);
        }

        if (!(
            trim($arHtmlControl['VALUE']) !== ''
            && ($arValue = @unserialize(htmlspecialcharsback($arHtmlControl['VALUE'])))
            && is_array($arValue)
            && isset($arValue['PRICE_FROM'], $arValue['PRICE_TO'])
        )) {
            $arValue = [
                'PRICE_FROM' => '',
                'PRICE_TO' => '',
            ];
        }
        
        $tpl = 'от <input type="text" name="#INPUT_NAME#[PRICE_FROM]" value="#VALUE_PRICE_FROM#">
                до <input type="text" name="#INPUT_NAME#[PRICE_TO]" value="#VALUE_PRICE_TO#">';

        $result = str_replace(
            [
                '#INPUT_NAME#',
                '#VALUE_PRICE_FROM#',
                '#VALUE_PRICE_TO#',
            ],
            [
                $arHtmlControl['NAME'],
                $arValue['PRICE_FROM'],
                $arValue['PRICE_TO'],
            ],
            $tpl
        );
        
        return $result;
    }

    /**
     * @param array|bool $arUserField
     * @param array $arHtmlControl
     * @param $bVarsFromForm
     * @return string
     */
    public function GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm)
    {
        return parent::GetSettingsHTML($arUserField, $arHtmlControl, $bVarsFromForm);
    }

    /**
     * @param array $arUserField
     * @param array $arHtmlControl
     * @return string
     */
    public function GetAdminListViewHTML($arUserField, $arHtmlControl)
    {
        if (
            trim($arHtmlControl['VALUE']) !== ''
            && ($arValue = @unserialize(htmlspecialcharsback($arHtmlControl['VALUE'])))
            && is_array($arValue)
            && isset($arValue['PRICE_FROM'], $arValue['PRICE_TO'])
        ) {
            return sprintf('<strong>%s</strong>%s', $arValue['PRICE_FROM'], $arValue['PRICE_TO']);
        }

        return '&nbsp;';
    }

    /**
     * @param array $arUserField
     * @param array $arHtmlControl
     * @return string|string[]
     */
    public function GetAdminListEditHTML($arUserField, $arHtmlControl)
    {
        return self::getEditRowHtml($arUserField, $arHtmlControl);
    }

    /**
     * @param array $arUserField
     * @return string
     */
    public function OnSearchIndex($arUserField)
    {
        if (is_array($arUserField['VALUE'])) {
            return implode("\r\n/",
                (is_array($arUserField['VALUE'])
                    ? implode("\r\n", $arUserField['VALUE'])
                    : $arUserField['VALUE']
                )
            );
        }

        return $arUserField['VALUE'];
    }

    /**
     * @param $arUserField
     * @param $value
     * @return string
     */
    public function OnBeforeSave($arUserField, $value)
    {
        if (
            is_array($value)
            && isset($value['PRICE_FROM'], $value['PRICE_TO'])
        ) {
            return serialize($value);
        }

        return '';
    }

    /**
     * @param array $arUserField
     * @param array $value
     * @return array
     */
    public function CheckFields($arUserField, $value)
    {
        return [];
    }
}
