<?php

namespace Natix\Service\Catalog\Bouquets\Service\Factory;

use Natix\Service\Catalog\Bouquets\Collection\ImageEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\ImageEntity;
use Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException;

/**
 * Фабрика для создания сущности с картинкой
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ImageFactory
{
    /**
     * Возвращает сущность картинки по её id
     * @param int $imageId
     * @param bool $isPreview
     * @param int|null $resizeWidth
     * @param int|null $resizeHeight
     * @return ImageEntity
     * @throws ImageFactoryException
     */
    public function build(
        int $imageId,
        bool $isPreview = false,
        int $resizeWidth = null,
        int $resizeHeight = null
    ): ImageEntity
    {
        if ($imageId <= 0) {
            throw new ImageFactoryException('$id должен быть больше 0');
        }

        $smallSrc = null;

        if ($resizeWidth && $resizeHeight) {
            $resizeImage = \CFile::ResizeImageGet(
                $imageId,
                [
                    'width' => $resizeWidth,
                    'height' => $resizeHeight,
                ],
                BX_RESIZE_IMAGE_PROPORTIONAL
            );

            $smallSrc = $resizeImage['src'];
        }

        return new ImageEntity($imageId, \CFile::GetPath($imageId), $smallSrc, $isPreview);
    }

    /**
     * Возвращает коллекцию картинок по их id
     * @param array $imageIds
     * @param bool $isPreview
     * @param int|null $resizeWidth
     * @param int|null $resizeHeight
     * @return ImageEntityCollection
     * @throws ImageFactoryException
     */
    public function buildByIds(
        array $imageIds,
        bool $isPreview = false,
        int $resizeWidth = null,
        int $resizeHeight = null
    ): ImageEntityCollection
    {
        $collection = new ImageEntityCollection();

        foreach ($imageIds as $imageId) {
            $collection->set(
                $imageId,
                $this->build($imageId, $isPreview, $resizeWidth, $resizeHeight)
            );
        }

        return $collection;
    }
}
