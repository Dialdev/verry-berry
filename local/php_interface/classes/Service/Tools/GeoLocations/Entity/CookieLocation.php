<?php


namespace Natix\Service\Tools\GeoLocations\Entity;

/**
 * Сущность местоположения из кук
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CookieLocation
{
    /** @var string */
    private $code;

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var bool */
    private $accept;

    public function __construct(string $code, string $name, string $type, bool $accept)
    {
        $this->code = $code;
        $this->name = $name;
        $this->type = $type;
        $this->accept = $accept;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isAccept(): bool
    {
        return $this->accept;
    }
}
