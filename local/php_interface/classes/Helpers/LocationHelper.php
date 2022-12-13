<?php

namespace Natix\Helpers;

use Bitrix\Main\Context;

/**
 * Хелпер для местоположений
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class LocationHelper
{
    /**
     * Возвращает код текущего местоположения
     * @return string|null
     */
    public static function getLocationCode(): ?string
    {
        $request = Context::getCurrent()->getRequest();
        
        $locationCode = $request->getCookie('location_code');

        $locationCode = !empty($locationCode)
            ? $locationCode
            : EnvironmentHelper::getParam('locations')['locationMoscowCode'];

        if ($request->get('location')) {
            $locationCode = htmlspecialcharsbx($request->get('location'));
        }

        return $locationCode;
    }
}
