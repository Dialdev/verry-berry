<?php

namespace Natix\Service\Tools\GeoLocations\Service;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Sale\Location\LocationTable;

class LocationService
{
    /**
     * Получение данных города по его guid (коду)
     *
     * @param string $locationCode - код местоположения
     * @return array - массив с ключами CITY_NAME,REGION_NAME,CODE,ID
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    public function getLocationByCode(string $locationCode): array
    {
        if (empty($locationCode)) {
            throw new \InvalidArgumentException('$locationCode должен быть не пустой строкой');
        }

        static $arCacheLocation = null;

        $locationCode = htmlspecialchars($locationCode);

        if (!isset($arCacheLocation[$locationCode])) {
            $arCacheLocation[$locationCode] = [];

            $cache = Cache::createInstance();

            $cacheId = md5($locationCode);
            $cacheDir = 'locationDataForCodes';

            if ($cache->initCache(3600 * 24 * 14, $cacheId, $cacheDir)) {
                $arCacheLocation[$locationCode] = $cache->getVars();
            } elseif ($cache->startDataCache()) {

                Loader::includeModule('sale');

                $arCacheLocation[$locationCode] = [];

                $locations = LocationTable::getList([
                    'select' => [
                        '*',
                        'CITY_NAME' => 'NAME.NAME',
                        'LANGUAGE_ID' => 'NAME.LANGUAGE_ID',
                    ],
                    'filter' => [
                        '=CODE' => $locationCode,
                        '=LANGUAGE_ID' => 'ru',
                    ],
                ]);

                if ($location = $locations->fetch()) {
                    $arCacheLocation[$locationCode] = $location;
                }

                $cache->endDataCache($arCacheLocation[$locationCode]);
            }
        }

        return $arCacheLocation[$locationCode];
    }

    /**
     * Возвращает все доступные местоположения
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getAllLocations(): array
    {
        return LocationTable::query()
            ->setSelect([
                '*',
                'CITY_NAME' => 'NAME.NAME',
                'LANGUAGE_ID' => 'NAME.LANGUAGE_ID',
            ])
            ->setFilter([
                '=LANGUAGE_ID' => 'ru',
            ])
            ->setCacheTtl(3600 * 24 * 14)
            ->exec()
            ->fetchAll();
    }
}