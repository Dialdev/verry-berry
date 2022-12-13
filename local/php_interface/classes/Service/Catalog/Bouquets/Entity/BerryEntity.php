<?php

namespace Natix\Service\Catalog\Bouquets\Entity;

use Natix\Service\Catalog\Bouquets\Collection\BerryEntityCollection;

/**
 * Сущность дополнительной ягоды, добавляемой в букет
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BerryEntity
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
     * @var SizeEntity
     */
    private $size;

    /**
     * @var ImageEntity|null
     */
    private $image;

    /**
     * @var bool
     */
    private $available;

    /**
     * @var PriceEntity
     */
    private $price;

    /**
     * @var int
     */
    private $sectionId;

    /**
     * @param int $id
     * @param string $name
     * @param string $cardName
     * @param SizeEntity $size
     * @param ImageEntity|null $image
     * @param bool $available
     * @param PriceEntity $priceEntity
     */
    public function __construct(
        int $id,
        string $name,
        string $cardName,
        SizeEntity $size,
        ?ImageEntity $image,
        bool $available,
        PriceEntity $priceEntity,
        int $sectionId
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->cardName = $cardName;
        $this->size = $size;
        $this->image = $image;
        $this->available = $available;
        $this->price = $priceEntity;
        $this->sectionId = $sectionId;
    }

    /**
     * @param BerryEntity $berryEntity
     * @return array
     */
    public static function toState(self $berryEntity): array
    {
        return [
            'id' => $berryEntity->getId(),
            'name' => $berryEntity->getName(),
            'card_name' => $berryEntity->getCardName(),
            'size' => SizeEntity::toState($berryEntity->getSize()),
            'image' => $berryEntity->getImage() !== null ? ImageEntity::toState($berryEntity->getImage()) : null,
            'available' => $berryEntity->isAvailable(),
            'price' => PriceEntity::toState($berryEntity->getPrice()),
            'section_id' => $berryEntity->getSectionId(),
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
     * @return SizeEntity
     */
    public function getSize(): SizeEntity
    {
        return $this->size;
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

    /**
     * @return PriceEntity
     */
    public function getPrice(): PriceEntity
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getSectionId(): int
    {
        return $this->sectionId;
    }

    /**
     * Проверяет, задан ли какой-либо размер у доп.ягоды
     * @return bool
     */
    public function isExistSize(): bool
    {
        return $this->size instanceof SizeEntity;
    }

    /**
     * Проверяет, задан ли переданный размер у доп.ягоды
     * @param SizeEntity $sizeEntity
     * @return bool
     */
    public function isContainsSize(SizeEntity $sizeEntity): bool
    {
        return $this->isExistSize() && $this->size->getId() === $sizeEntity->getId();
    }
}
