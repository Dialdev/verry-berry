<?php

namespace Natix\Service\Catalog\Bouquets\Entity;

use Natix\Service\Catalog\Bouquets\Collection\BerryEntityCollection;
use Natix\Service\Catalog\Bouquets\Collection\ImageEntityCollection;

/**
 * Сущность комплекта
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetEntity
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
     * @var string
     */
    private $code;

    /**
     * @var ImageEntity|null
     */
    private $image;

    /**
     * @var PriceEntity
     */
    private $price;

    /**
     * @var SizeEntity
     */
    private $size;

    /**
     * Букет, входящий в комплект
     *
     * @var BouquetEntity
     */
    private $bouquet;

    /**
     * Коллекция дополнительных ягод, входящих в комплект
     *
     * @var BerryEntityCollection|null
     */
    private $berries;

    /**
     * Упаковка, входящая в комплект
     *
     * @var PackingEntity
     */
    private $packing;

    /**
     * Дополнительные картинки комплекта
     *
     * @var ImageEntityCollection|null
     */
    private $dopImages;

    /**
     * @var string|null
     */
    private $articul;

    /**
     * @var int
     */
    private $sectionId;

    private string $sectionsTextChain;

    /**
     * @param int                        $id
     * @param string                     $name
     * @param string                     $cardName
     * @param string                     $code
     * @param ImageEntity|null           $image
     * @param PriceEntity                $price
     * @param SizeEntity|null            $size
     * @param BouquetEntity|null         $bouquet
     * @param BerryEntityCollection|null $berries
     * @param PackingEntity|null         $packing
     * @param ImageEntityCollection|null $dopImages
     * @param string|null                $articul
     * @param int|null                   $sectionId
     * @param string|null                $sectionsTextChain
     */
    public function __construct(
        int $id,
        string $name,
        string $cardName,
        string $code,
        ?ImageEntity $image,
        PriceEntity $price,
        ?SizeEntity $size,
        ?BouquetEntity $bouquet,
        ?BerryEntityCollection $berries,
        ?PackingEntity $packing,
        ?ImageEntityCollection $dopImages,
        ?string $articul,
        ?int $sectionId,
        ?string $sectionsTextChain
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->cardName = $cardName;
        $this->code = $code;
        $this->image = $image;
        $this->price = $price;
        $this->size = $size;
        $this->bouquet = $bouquet;
        $this->berries = $berries;
        $this->packing = $packing;
        $this->dopImages = $dopImages;
        $this->articul = $articul;
        $this->sectionId = $sectionId;
        $this->sectionsTextChain = $sectionsTextChain;
    }

    /**
     * @param SetEntity $setEntity
     * @return array
     */
    public static function toState(self $setEntity): array
    {
        return [
            'id'                  => $setEntity->getId(),
            'name'                => $setEntity->getName(),
            'card_name'           => $setEntity->getCardName(),
            'code'                => $setEntity->getCode(),
            'url'                 => $setEntity->getUrl(),
            'image'               => $setEntity->getImage() instanceof ImageEntity
                ? ImageEntity::toState($setEntity->getImage())
                : null,
            'available'           => $setEntity->isAvailable(),
            'price'               => $setEntity->getPrice() instanceof PriceEntity
                ? PriceEntity::toState($setEntity->getPrice())
                : null,
            'size'                => $setEntity->getSize() instanceof SizeEntity
                ? SizeEntity::toState($setEntity->getSize())
                : null,
            'bouquet'             => $setEntity->getBouquet() instanceof BouquetEntity
                ? BouquetEntity::toState($setEntity->getBouquet())
                : null,
            'berries'             => $setEntity->getBerries() instanceof BerryEntityCollection
                ? BerryEntityCollection::toState($setEntity->getBerries())
                : null,
            'packing'             => $setEntity->getPacking() instanceof PackingEntity
                ? PackingEntity::toState($setEntity->getPacking())
                : null,
            'dop_images'          => $setEntity->getDopImages() instanceof ImageEntityCollection
                ? ImageEntityCollection::toState($setEntity->getDopImages())
                : null,
            'articul'             => $setEntity->getArticul(),
            'section_id'          => $setEntity->getSectionId(),
            'sections_text_chain' => $setEntity->getSectionsTextChain(),
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
     * @return PriceEntity
     */
    public function getPrice(): PriceEntity
    {
        return $this->price;
    }

    /**
     * @return SizeEntity|null
     */
    public function getSize(): ?SizeEntity
    {
        return $this->size;
    }

    /**
     * @return BouquetEntity|null
     */
    public function getBouquet(): ?BouquetEntity
    {
        return $this->bouquet;
    }

    /**
     * @return BerryEntityCollection|null
     */
    public function getBerries(): ?BerryEntityCollection
    {
        return $this->berries;
    }

    /**
     * @return PackingEntity|null
     */
    public function getPacking(): ?PackingEntity
    {
        return $this->packing;
    }

    /**
     * @return ImageEntityCollection|null
     */
    public function getDopImages(): ?ImageEntityCollection
    {
        return $this->dopImages;
    }

    /**
     * @return string|null
     */
    public function getArticul(): ?string
    {
        return $this->articul;
    }

    /**
     * @return int
     */
    public function getSectionId(): int
    {
        return $this->sectionId;
    }

    /**
     * @return string
     */
    public function getSectionsTextChain(): string
    {
        return $this->sectionsTextChain;
    }

    /**
     * Проверяет, в наличии ли комплект
     * Коплект в наличии, если вхоядяшие в него букет, дополнительные ягоды и упаковка в наличии
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $isAvailableBouquet = $this->getBouquet() instanceof BouquetEntity && $this->getBouquet()->isAvailable();

        $isAvailablePacking = $this->getPacking() instanceof PackingEntity && $this->getPacking()->isAvailable();

        $isAvailableBerries = true;

        if ($this->getBerries() instanceof BerryEntityCollection) {
            /** @var BerryEntity $berryEntity */
            foreach ($this->getBerries()->getIterator() as $berryEntity) {
                if (!$berryEntity->isAvailable()) {
                    $isAvailableBerries = false;
                    break;
                }
            }
        }

        return $isAvailableBouquet && $isAvailablePacking && $isAvailableBerries;
    }

    /**
     * Возвращает ссылку на карточку товара комплекта
     *
     * @return string
     */
    public function getUrl(): string
    {
        return sprintf('/product/%s/', $this->code);
    }

    /**
     * Проверяет, находится ли комплект внутри раздела, объединяющего схожие букеты
     *
     * @return bool
     */
    public function isInSection(): bool
    {
        return $this->sectionId > 0;
    }

    /**
     * Проверяет, находится ли основа букета в комплекте
     *
     * @param BouquetEntity $bouquetEntity
     * @return bool
     */
    public function isContainsBouquet(BouquetEntity $bouquetEntity): bool
    {
        return $this->isExistBouquet() && $this->bouquet->getId() === $bouquetEntity->getId();
    }

    /**
     * Проверяет, имееется ли в комплекте какая либо основа букета
     *
     * @return bool
     */
    public function isExistBouquet(): bool
    {
        return $this->bouquet instanceof BouquetEntity;
    }

    /**
     * Проверяет, имеются ли в комплекте доп.ягоды
     *
     * @return bool
     */
    public function isExistBerries(): bool
    {
        return $this->berries instanceof BerryEntityCollection;
    }

    /**
     * Проверяет, находится ли дополнительная ягода в комплекте
     *
     * @param BerryEntity $berryEntity
     * @return bool
     */
    public function isContainsBerry(BerryEntity $berryEntity): bool
    {
        return $this->isExistBerries() && $this->berries->has($berryEntity->getId());
    }

    /**
     * Проверяет, имеется ли в комплекте какая либо упаковка
     *
     * @return bool
     */
    public function isExistPacking(): bool
    {
        return $this->packing instanceof PackingEntity;
    }

    /**
     * Проверяет, добавлена ли упаковка в комплект
     *
     * @param PackingEntity $packingEntity
     * @return bool
     */
    public function isContainsPacking(PackingEntity $packingEntity): bool
    {
        return $this->isExistPacking() && $this->packing->getId() === $packingEntity->getId();
    }

    /**
     * Проверяет, задан ли переданный размер  у комплекта
     *
     * @return bool
     */
    public function isExistSize(): bool
    {
        return $this->size instanceof SizeEntity;
    }

    /**
     * Проверяет, задан ли переданный размер  у комплекта
     *
     * @param SizeEntity $sizeEntity
     * @return bool
     */
    public function isContainsSize(SizeEntity $sizeEntity): bool
    {
        return $this->isExistSize() && $this->size->getId() === $sizeEntity->getId();
    }
}
