<?php

namespace Natix\Service\Catalog\Bouquets\Service\Factory;

use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Entity\Orm\PackingTable;
use Natix\Service\Catalog\Bouquets\Collection\PackingEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\PackingEntity;
use Natix\Service\Catalog\Bouquets\Exception\ImageFactoryException;
use Natix\Service\Catalog\Bouquets\Exception\PackingFactoryException;

/**
 * Фабрика для создания объектов сущностей упаковок
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PackingFactory
{
    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var PackingEntityCollection
     */
    private static $packingMap;

    /**
     * @param IblockFinder $iblockFinder
     * @param ImageFactory $imageFactory
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     */
    public function __construct(IblockFinder $iblockFinder, ImageFactory $imageFactory)
    {
        $this->iblockFinder = $iblockFinder;
        $this->imageFactory = $imageFactory;

        $this->preparePackingMap();
    }

    /**
     * Создаёт и возвращает объект с упаковкой по её id
     * @param int $id
     * @return PackingEntity
     * @throws PackingFactoryException
     */
    public function buildById(int $id): PackingEntity
    {
        if ($id <= 0) {
            throw new PackingFactoryException('$id должен быть больше 0');
        }

        if (!self::$packingMap->has($id)) {
            throw new PackingFactoryException(sprintf('Упаковка с id "%s" не найдена', $id));
        }

        return self::$packingMap[$id];
    }

    /**
     * Создает коллекцию всех имеющихся упаковок
     * @return PackingEntityCollection
     */
    public function buildAllPacking(): PackingEntityCollection
    {
        return self::$packingMap;
    }

    /**
     * Создает сущность упаковки из переданных данных
     * @param array $packing
     * @return PackingEntity
     * @throws PackingFactoryException
     * @throws ImageFactoryException
     */
    public function buildByRow(array $packing): PackingEntity
    {
        if (empty($packing)) {
            throw new PackingFactoryException('$packing должен быть не пустым массивом');
        }

        return new PackingEntity(
            $packing['ID'],
            $packing['NAME'],
            $packing['PROPERTY_CARD_NAME'],
            $packing['PREVIEW_PICTURE']
                ? $this->imageFactory->build($packing['PREVIEW_PICTURE'], true)
                : null,
            ((int)$packing['PROPERTY_AVAILABLE'] === 1)
        );
    }

    /**
     * Подготавливает маппинг с коллекцией всех упаковок
     * @throws FinderEmptyValueException
     * @throws ImageFactoryException
     * @throws PackingFactoryException
     */
    private function preparePackingMap(): void
    {
        if (self::$packingMap === null) {
            $packaging = PackingTable::query()
                ->setSelect([
                    'ID',
                    'NAME',
                    'PREVIEW_PICTURE',
                    'PROPERTY_AVAILABLE',
                    'PROPERTY_CARD_NAME',
                ])
                ->setFilter([
                    '=IBLOCK_ID' => $this->iblockFinder->packing(),
                ])
                ->exec()
                ->fetchAll();

            self::$packingMap = new PackingEntityCollection();

            foreach ($packaging as $packing) {
                self::$packingMap->set($packing['ID'], $this->buildByRow($packing));
            }
        }
    }
}
