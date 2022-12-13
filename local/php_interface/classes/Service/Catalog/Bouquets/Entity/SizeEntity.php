<?php

namespace Natix\Service\Catalog\Bouquets\Entity;

/**
 * Сущность размера букета и дополнительных ягод
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SizeEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @param int $id
     * @param string $name
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @param SizeEntity $sizeEntity
     * @return array
     */
    public static function toState(SizeEntity $sizeEntity): array
    {
        return [
            'id' => $sizeEntity->getId(),
            'name' => $sizeEntity->getName(),
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
