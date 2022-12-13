<?php

namespace Natix\Service\Tools\GeoLocations\Service;

use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Web\Cookie;
use Natix\Service\Tools\GeoLocations\Entity\CookieLocation;

/**
 * Менеджер для управления cookie геолокации
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CookieManager
{
    const COOKIE_CODE = 'location_code';
    const COOKIE_NAME = 'location_name';
    const COOKIE_TYPE = 'location_type';
    const COOKIE_ACCEPT = 'location_accept';
    const COOKIE_ACCEPT_TRUE_VALUE = 'Y';

    /** @var HttpRequest */
    private $request;

    /** @var HttpResponse */
    private $response;

    /** @var UserLocation */
    private $userLocation;

    /** @var int */
    private $cookieExpires;

    /** @var null|string */
    private $httpHost;

    public function __construct(UserLocation $userLocation)
    {
        $this->request = Context::getCurrent()->getRequest();
        $this->response = Context::getCurrent()->getResponse();

        $this->cookieExpires = time() + 60 * 60 * 24 * 30; // на месяц
        $this->httpHost = Context::getCurrent()->getServer()->getHttpHost();

        $this->userLocation = $userLocation;
    }

    /**
     * Возвращает значения cookies
     *
     * @return CookieLocation
     */
    public function get(): CookieLocation
    {
        return new CookieLocation(
            $this->request->getCookie(static::COOKIE_CODE) ?? '',
            $this->request->getCookie(static::COOKIE_NAME) ?? '',
            $this->request->getCookie(static::COOKIE_TYPE) ?? '',
            ($this->request->getCookie(static::COOKIE_ACCEPT) === 'Y')
        );
    }

    /**
     * Сохраняет code в куку
     *
     * @param $value
     */
    public function setCode($value)
    {
        $this->set(static::COOKIE_CODE, $value);

        $this->userLocation->setLocationCode($value);
    }

    /**
     * Сохраняет name в куку
     *
     * @param $value
     */
    public function setName($value)
    {
        $this->set(static::COOKIE_NAME, $value);
    }

    /**
     * Сохраняет type в куку
     *
     * @param $value
     */
    public function setType($value)
    {
        $this->set(static::COOKIE_TYPE, $value);
    }

    /**
     * Сохраняет accept в куку
     *
     * @param $value
     */
    public function setAccept($value)
    {
        $this->set(static::COOKIE_ACCEPT, $value);
    }

    /**
     * Сохраняет куку
     *
     * @param string $name
     * @param $value
     */
    private function set(string $name, $value)
    {
        $cookie = new Cookie($name, $value, $this->cookieExpires);
        $cookie->setDomain($this->httpHost);
        $cookie->setHttpOnly(false);

        $this->response->addCookie($cookie);
    }
}