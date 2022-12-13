<?php

namespace helpers;

class SectionsHelper
{
    /**
     * Находится ли секция в городе пользователя?
     *
     * @param string $sectionUrl
     * @return bool|null
     */
    public static function isSectionInRightLocation(string $sectionUrl): ?bool
    {
        $path = parse_url($sectionUrl, PHP_URL_PATH);

        if (!$path)
            return null;

        $path = trim($path, '/');

        $code = current(array_slice(explode('/', $path), -1, 1));

        if (!$code)
            return null;

        $section = \CIBlockSection::GetList(false, ['IBLOCK_ID' => 5, 'CODE' => $code], false, ['UF_CITY']);

        $section = $section->GetNext();

        if (!$section)
            return null;

        $fields = \CUserFieldEnum::GetList(false, array('FIELD_NAME' => 'UF_CITY'));

        $xmlIds = [];

        while ($field = $fields->GetNext()) {
            if (in_array($field['ID'], $section['UF_CITY']))
                $xmlIds[] = $field['XML_ID'];
        }

        $location = self::getUserLocation();

        if (!$location or !$xmlIds)
            return null;

        return in_array($location, $xmlIds);
    }

    /**
     * Получить город пользователя
     *
     * @return string|null
     */
    public static function getUserLocation(): ?string
    {
        global $APPLICATION;

        $location = $_GET['location'] ?? $APPLICATION->get_cookie('location_code');

        return $location ?: null;
    }
}
