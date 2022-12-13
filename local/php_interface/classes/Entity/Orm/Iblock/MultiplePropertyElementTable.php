<?php

namespace Natix\Entity\Orm\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main;
use Bitrix\Main\Entity\Base;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
abstract class MultiplePropertyElementTable extends Entity\DataManager
{
    /**
     * @var int
     */
    protected static $iblockId;

    /**
     * @var array
     */
    protected static $entities = [];

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return sprintf('b_iblock_element_prop_m%s', static::$iblockId);
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws Main\ArgumentException
     */
    public static function getMap(): array
    {
        return [
            'ID' => [
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => 'ID',
            ],
            'IBLOCK_ELEMENT_ID' => [
                'data_type' => 'integer',
                'required' => true,
            ],
            new Entity\ReferenceField(
                'ELEMENT',
                '\Base',
                ['=this.IBLOCK_ELEMENT_ID' => 'ref.ID']
            ),
            'IBLOCK_PROPERTY_ID' => [
                'data_type' => 'integer',
                'required' => true,
            ],
            'VALUE' => [
                'data_type' => 'string',
                'required' => true,
            ],
            'VALUE_ENUM' => [
                'data_type' => 'integer',
                'required' => true,
            ],
            'VALUE_NUM' => [
                'data_type' => 'float',
                'required' => true,
            ],
            'DESCRIPTION' => [
                'data_type' => 'string',
            ],
            new Entity\ReferenceField(
                'PROPERTY',
                '\Bitrix\Iblock\Property',
                ['this.IBLOCK_PROPERTY_ID' => 'ref.ID']
            ),
            new Entity\ExpressionField(
                'CODE',
                '%s',
                'PROPERTY.CODE'
            ),
        ];
    }

    /**
     * @param int $iblockId
     * @param array $parameters
     * @return Base
     * @throws Main\ArgumentException
     */
    public static function createEntity(int $iblockId): Base
    {
        self::$iblockId = $iblockId;

        if ($iblockId <= 0) {
            throw new Main\ArgumentException('$iblockId should be integer');
        }

        $entityName = sprintf('MultiplePropertyIblock%sTable', $iblockId);

        if (isset(self::$entities[$entityName])) {
            return self::$entities[$entityName];
        }

        $entity = Base::compileEntity(
            $entityName,
            self::getMap(),
            ['table_name' => self::getTableName()]
        );

        self::$entities[$entityName] = $entity;

        return $entity;
    }

    /**
     * @return string
     */
    public static function getClassName(): string
    {
        return __CLASS__;
    }
}
