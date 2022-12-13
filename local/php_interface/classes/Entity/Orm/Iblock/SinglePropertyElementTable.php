<?php

namespace Natix\Entity\Orm\Iblock;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Main;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\Base;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
abstract class SinglePropertyElementTable extends Entity\DataManager
{
    protected static $iblockId;

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return sprintf('b_iblock_element_prop_s%s', static::$iblockId);
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     * @throws \Exception
     */
    public static function getMap(): array
    {
        $metadata = ElementTable::getMetadata(static::$iblockId);

        $map = [
            'IBLOCK_ELEMENT_ID' => [
                'data_type' => 'integer',
                'primary' => true,
            ]
        ];

        foreach ($metadata['props'] as $arProp) {
            if ($arProp['MULTIPLE'] === 'Y') {
                continue;
            }

            switch ($arProp['PROPERTY_TYPE']) {
                case PropertyTable::TYPE_NUMBER:
                    $map[] = new Entity\FloatField(
                        $arProp['CODE'],
                        ['column_name' => sprintf('PROPERTY_%s', $arProp['ID'])]
                    );
                    $map[] = new Entity\StringField(
                        sprintf( '%s_DESCRIPTION', $arProp['CODE']),
                        ['column_name' => sprintf('DESCRIPTION_%s', $arProp['ID'])]
                    );
                    break;

                case PropertyTable::TYPE_LIST:
                case PropertyTable::TYPE_ELEMENT:
                case PropertyTable::TYPE_SECTION:
                    $map[] = new Entity\IntegerField(
                        $arProp['CODE'],
                        ['column_name' => sprintf('PROPERTY_%s', $arProp['ID'])]
                    );
                    $map[] = new Entity\StringField(
                        sprintf('%s_DESCRIPTION', $arProp['CODE']),
                        ['column_name' => sprintf('DESCRIPTION_%s', $arProp['ID'])]
                    );

                    break;

                case PropertyTable::TYPE_STRING:
                default:
                    $map[] = new Entity\StringField(
                        $arProp['CODE'],
                        ['column_name' => sprintf('PROPERTY_%s', $arProp['ID'])]
                    );
                    $map[] = new Entity\StringField(
                        sprintf('%s_DESCRIPTION', $arProp['CODE']),
                        ['column_name' => sprintf('DESCRIPTION_%s', $arProp['ID'])]
                    );
                    break;
            }
        }

        return $map;
    }

    /**
     * @param int $iblockId
     * @return Base
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     */
    public static function createEntity(int $iblockId): Base
    {
        self::$iblockId = $iblockId;

        if ($iblockId <= 0) {
            throw new Main\ArgumentException('$iblockId should be integer');
        }

        $entityName = sprintf('SinglePropertyIblock%sTable', $iblockId);

        if (class_exists($entityName)) {
            $entity = $entityName::getEntity();
        } else {
            $entity = Main\Entity\Base::compileEntity(
                $entityName,
                self::getMap(),
                ['table_name' => self::getTableName()]
            );
        }

        return $entity;
    }
}
