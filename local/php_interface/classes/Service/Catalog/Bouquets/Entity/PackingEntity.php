<?php

namespace Natix\Service\Catalog\Bouquets\Entity;

/**
 * Сущность упаковки для букета
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PackingEntity
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
     * @var string
     */
    private $cardName;

    /**
     * @var ImageEntity|null
     */
    private $image;

    /**
     * @var bool
     */
    private $available;

    /**
     * @param int $id
     * @param string $name
     * @param string $cardName
     * @param ImageEntity|null $image
     * @param bool $available
     */
    public function __construct(
        int $id,
        string $name,
        string $cardName,
        ?ImageEntity $image,
        bool $available
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->cardName = $cardName;
        $this->image = $image;
        $this->available = $available;
    }

    /**
     * @param PackingEntity $packingEntity
     * @return array
     */
    public static function toState(PackingEntity $packingEntity): array
    {
        return [
            'id' => $packingEntity->getId(),
            'name' => $packingEntity->getName(),
            'card_name' => $packingEntity->getCardName(),
            'image' => $packingEntity->getImage() !== null ? ImageEntity::toState($packingEntity->getImage()) : null,
            'available' => $packingEntity->isAvailable(),
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

    /**
     * @return string
     */
    public function getCardName(): string
    {
        return $this->cardName;
    }

    /**
     * @return ImageEntity|null
     */
    public function getImage(): ?ImageEntity
    {
        return $this->image;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->available;
    }
}
