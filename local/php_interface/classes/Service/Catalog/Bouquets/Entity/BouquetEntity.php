<?php

namespace Natix\Service\Catalog\Bouquets\Entity;

/**
 * Сущность основы букета
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BouquetEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * Название букета
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $code;

    /**
     * Фото букета
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
     * @var SizeEntity
     */
    private $size;

    /**
     * @param int $id
     * @param string $name
     * @param string $code
     * @param ImageEntity|null $imageEntity
     * @param bool $available
     * @param PriceEntity $priceEntity
     * @param SizeEntity $sizeEntity
     */
    public function __construct(
        int $id,
        string $name,
        string $code,
        ?ImageEntity $imageEntity,
        bool $available,
        PriceEntity $priceEntity,
        SizeEntity $sizeEntity
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->image = $imageEntity;
        $this->available = $available;
        $this->price = $priceEntity;
        $this->size = $sizeEntity;
    }

    /**
     * @param BouquetEntity $bouquetEntity
     * @return array
     */
    public static function toState(BouquetEntity $bouquetEntity): array
    {
        return [
            'id' => $bouquetEntity->getId(),
            'name' => $bouquetEntity->getName(),
            'code' => $bouquetEntity->getCode(),
            'image' => $bouquetEntity->getImage() instanceof ImageEntity
                ? ImageEntity::toState($bouquetEntity->getImage())
                : null,
            'available' => $bouquetEntity->isAvailable(),
            'price' => PriceEntity::toState($bouquetEntity->getPrice()),
            'size' => $bouquetEntity->getSize() instanceof SizeEntity
                ? SizeEntity::toState($bouquetEntity->getSize())
                : null,
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
    public function getCode(): string
    {
        return $this->code;
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
     * @return SizeEntity
     */
    public function getSize(): SizeEntity
    {
        return $this->size;
    }

    /**
     * Проверяет, задан ли какой-либо размер у основы букета
     * @return bool
     */
    public function isExistSize(): bool
    {
        return $this->size instanceof SizeEntity;
    }

    /**
     * Проверяет, задан ли переданный размер  у основы букета
     * @param SizeEntity $sizeEntity
     * @return bool
     */
    public function isContainsSize(SizeEntity $sizeEntity): bool
    {
        return $this->isExistSize() && $this->size->getId() === $sizeEntity->getId();
    }
}
