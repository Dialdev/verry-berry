<?php

namespace Natix\Service\Catalog\Bouquets\Entity;

/**
 * Сущность картинки
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ImageEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $src;

    /**
     * @var string|null
     */
    private $smallSrc;

    /**
     * Фото товара
     * @var bool
     */
    private $isPreview;

    /**
     * @param int $id
     * @param string $src
     * @param string|null $smallSrc
     * @param bool $isPreview
     */
    public function __construct(int $id, string $src, ?string $smallSrc, bool $isPreview)
    {
        $this->id = $id;
        $this->src = $src;
        $this->smallSrc = $smallSrc;
        $this->isPreview = $isPreview;
    }

    /**
     * @param ImageEntity $imageEntity
     * @return array
     */
    public static function toState(ImageEntity $imageEntity): array
    {
        return [
            'id' => $imageEntity->getId(),
            'src' => $imageEntity->getSrc(),
            'small_src' => $imageEntity->getSmallSrc(),
            'is_preview' => $imageEntity->isPreview(),
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
    public function getSrc(): string
    {
        return $this->src;
    }

    /**
     * @return string|null
     */
    public function getSmallSrc(): ?string
    {
        return $this->smallSrc;
    }

    /**
     * @return bool
     */
    public function isPreview(): bool
    {
        return $this->isPreview;
    }
}
