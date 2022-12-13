<?php

namespace Natix\Service\Catalog\Bouquets\Service\Factory;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Data\Bitrix\Finder\Iblock\IblockFinder;
use Natix\Service\Catalog\Bouquets\Collection\SizeEntityCollection;
use Natix\Service\Catalog\Bouquets\Entity\SizeEntity;
use Natix\Service\Catalog\Bouquets\Exception\SizeFactoryException;

/**
 * Фабрика для создания объектов сущностей с размерами
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SizeFactory
{
    /**
     * @var IblockFinder
     */
    private $iblockFinder;

    /**
     * @var SizeEntityCollection
     */
    private static $sizeMap;

    /**
     * @param IblockFinder $iblockFinder
     * @throws FinderEmptyValueException
     * @throws LoaderException
     */
    public function __construct(IblockFinder $iblockFinder)
    {
        $this->iblockFinder = $iblockFinder;

        $this->preparaSizeMap();
    }

    /**
     * Создаёт и возвращает объект с размером по его id
     * @param int $id
     * @return SizeEntity
     * @throws SizeFactoryException
     */
    public function buildById(int $id): SizeEntity
    {
        if ($id <= 0) {
            throw new SizeFactoryException('$id должен быть больше 0');
        }

        if (!self::$sizeMap->has($id)) {
            throw new SizeFactoryException(sprintf('Размер с id "%s" не найден', $id));
        }

        return self::$sizeMap[$id];
    }

    /**
     * Создаёт коллекцию со всеми имеющимися размерами букетов
     * @return SizeEntityCollection
     */
    public function buildAllSizes(): SizeEntityCollection
    {
        return self::$sizeMap;
    }

    /**
     * Подготавливает маппинг с коллекцией всех размеров
     * @throws FinderEmptyValueException
     * @throws LoaderException
     */
    private function preparaSizeMap(): void
    {
        if (self::$sizeMap === null) {
            Loader::includeModule('iblock');

            $sizes = ElementTable::query()
                ->setSelect(['ID', 'NAME'])
                ->setFilter([
                    '=IBLOCK_ID' => $this->iblockFinder->sizes(),
                ])
                ->exec()
                ->fetchAll();

            self::$sizeMap = new SizeEntityCollection();

            foreach ($sizes as $size) {
                self::$sizeMap->set(
                    $size['ID'],
                    new SizeEntity($size['ID'], $size['NAME'])
                );
            }
        }
    }
}