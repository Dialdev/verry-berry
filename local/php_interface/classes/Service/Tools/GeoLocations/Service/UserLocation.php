<?php

namespace Natix\Service\Tools\GeoLocations\Service;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class UserLocation
{
    /**
     * @var null|string
     */
    private $locationCode;

    private $saintPetersburgFias = 'c2deb16a-0330-4f05-821f-1d09c93331e6';

    /**
     * @param string $locationCode
     */
    public function setLocationCode(string $locationCode)
    {
        $this->locationCode = $locationCode;
    }

    /**
     * @return null|string
     */
    public function getLocationCode()
    {
        return $this->locationCode;
    }

    public function isSaintPetersburg(): bool
    {
        return $this->locationCode === $this->saintPetersburgFias;
    }
}
