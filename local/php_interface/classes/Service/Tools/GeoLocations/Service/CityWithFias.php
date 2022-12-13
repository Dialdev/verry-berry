<?php


namespace Natix\Service\Tools\GeoLocations\Service;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Sale\Location\LocationTable;

/**
 * Класс для получения местоположений города
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CityWithFias
{
    /**
     * Получает список городов по названию
     * @param string $cityName
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getCities(string $cityName): array
    {
        if (strlen($cityName) <= 0) {
            throw new \InvalidArgumentException('$cityName должен быть непустой строкой');
        }

        $query = LocationTable::query()
            ->setSelect([
                'CODE',
                'CITY_NAME' => 'CITY_LANG.NAME',
            ])
            ->setFilter([
                '=CITY_NAME' => $cityName
            ])
            ->registerRuntimeField('CITY_LANG', [
                'data_type' => \Bitrix\Sale\Location\Name\LocationTable::class,
                'reference' => [
                    '=this.CITY_ID' => 'ref.LOCATION_ID',
                    '=ref.LANGUAGE_ID' => new SqlExpression('?s', 'ru'),
                ],
                'join_type' => 'inner',
            ]);

        $iterator = $query->exec();

        $result = [];

        while ($item = $iterator->fetch()) {
            $result[] = [
                'name' => $item['CITY_NAME'],
                'code' => $item['CODE'],
            ];
        }

        return $result;
    }
}
