<?php

namespace Natix\Component;

use Bitrix\Main\Service\GeoIp\Manager;
use Ipgeobase\IpGeobase;
use Natix\Helpers\EnvironmentHelper;
use Natix\Service\Tools\GeoLocations\Entity\CookieLocation;
use Natix\Service\Tools\GeoLocations\Service\CityWithFias;
use Natix\Service\Tools\GeoLocations\Service\CookieManager;
use Natix\Service\Tools\GeoLocations\Service\LocationService;

/**
 * Компонент геолокации
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class GeoLocation extends CommonComponent
{
    /** @var array */
    protected $needModules = ['sale'];

    /** @var IpGeobase */
    private $ipGeobase;

    /** @var LocationService */
    private $locationService;

    /** @var CookieManager */
    private $cookieManager;

    /** @var CityWithFias */
    private $cityWithFias;

    /** @var array */
    private $location;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->ipGeobase = \Natix::$container->get(IpGeobase::class);
        $this->locationService = \Natix::$container->get(LocationService::class);
        $this->cookieManager = \Natix::$container->get(CookieManager::class);
        $this->cityWithFias = \Natix::$container->get(CityWithFias::class);
    }

    protected function executeMain()
    {
        $this->arResult['LOCATIONS_ALL'] = $this->locationService->getAllLocations();

        if (!empty($this->request->get('location'))) {
            $this->location = $this->locationService->getLocationByCode($this->request->get('location'));
        } else {
            $cookieLocation = $this->cookieManager->get();
        }

        if (
            isset($cookieLocation)
            && $cookieLocation instanceof CookieLocation
            && !empty($cookieLocation->getCode())
        ) {
            $this->arResult['LOCATION'] = $this->convertCookieLocationToArray($cookieLocation);
            $this->arResult['IS_ACCEPTED'] = $cookieLocation->isAccept();
        } else {
            if (!empty($this->location)) {
                $value = CookieManager::COOKIE_ACCEPT_TRUE_VALUE;
                $this->setAccept($value);
                $this->arResult['IS_ACCEPTED'] = $value;
            } else {
                $this->location = $this->detectLocation();
            }

            // если ничего не нашли - подставляем местоположение по умолчанию
            if (empty($this->location)) {
                $this->location = $this->getDefaultLocation();
            }

            $this->arResult['LOCATION'] = $this->location;

            $this->setCookies();
        }

        $this->cropCityType();
    }

    /**
     * Конвертирует контейнер в массив
     *
     * @param CookieLocation $cookieLocation
     * @return array
     */
    private function convertCookieLocationToArray(CookieLocation $cookieLocation): array
    {
        return [
            'CODE' => $cookieLocation->getCode(),
            'CITY_NAME' => $cookieLocation->getName(),
            'CITY_TYPE' => $cookieLocation->getType(),
        ];
    }

    /**
     * Сохраняет accept в куку
     *
     * @param $value
     */
    private function setAccept($value)
    {
        static $processed = false;

        if ($processed === false) {
            $this->cookieManager->setAccept($value);

            $processed = true;
        }
    }

    /**
     * Определяет местоположение по ip
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     */
    private function detectLocation(): array
    {
        $ipAddress = Manager::getRealIp();

        $data = $this->ipGeobase->lookup($ipAddress);

        if (!$data) {
            return [];
        }

        $this->arResult['DATA'] = $data;

        if (is_string($data->city) && $data->city !== '') {
            $cities = $this->cityWithFias->getCities($data->city);

            foreach ($cities as $city) {
                $name = strtolower($city['name']);

                $region = $this->cropRegion(
                    is_string($data->region) ? strtolower($data->region) : ''
                );

                if (strpos($name, $region) !== false) {
                    $location = $this->locationService->getLocationByCode($city['code']);

                    return is_array($location) ? $location : [];
                }
            }
        }

        return [];
    }

    /**
     * Возвращает местоположение по умолчанию
     *
     * @return array
     */
    private function getDefaultLocation()
    {
        return [
            'CODE' => EnvironmentHelper::getParam('locations')['locationMoscowCode'],
            'CITY_NAME' => 'Москва',
            'CITY_TYPE' => 'г',
        ];
    }

    /**
     * Устанавливаем куки
     */
    private function setCookies()
    {
        static $processed = false;

        if ($processed === false) {
            $this->cookieManager->setCode($this->arResult['LOCATION']['CODE']);
            $this->cookieManager->setName($this->arResult['LOCATION']['CITY_NAME']);

            $processed = true;
        }
    }

    /**
     * Обрезает тип города
     */
    private function cropCityType()
    {
        static $processed = false;

        $this->arResult['LOCATION']['CITY_TYPE'] = 'г';

        if (preg_match('/(.*)(\s)(.+)$/', $this->arResult['LOCATION']['CITY_NAME'], $arCity)) {
            if (!empty($arCity[1]) && !empty($arCity[3])) {
                $this->arResult['LOCATION']['CITY_NAME'] = $arCity[1];
                $this->arResult['LOCATION']['CITY_TYPE'] = $arCity[3];
            }
        }

        if ($processed === false && $this->cookieManager->get()->getType() !== $this->arResult['LOCATION']['CITY_TYPE']) {
            $this->cookieManager->setType($this->arResult['LOCATION']['CITY_TYPE']);

            $processed = true;
        }
    }

    /**
     * Скоращает обозначения регионов, чтобы можно было сравнить их со строкой из getCities
     *
     * @param string $region
     * @return mixed
     */
    private function cropRegion(string $region)
    {
        return str_replace(
            [
                ' область',
                ' республика',
            ],
            [
                ' обл',
                ' респ',
            ],
            $region
        );
    }
}
