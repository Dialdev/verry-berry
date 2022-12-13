<?php

namespace Natix\Entity\Orm;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\FloatField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

/**
 * ORM для bitrix-таблицы b_catalog_product_sets
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BitrixCatalogProductSetsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_catalog_product_sets';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws \Exception
     */
    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new IntegerField('TYPE'),
            new IntegerField('SET_ID'),
            new StringField('ACTIVE'),
            new IntegerField('OWNER_ID'),
            new IntegerField('ITEM_ID'),
            new IntegerField('QUANTITY'),
            new IntegerField('MEASURE'),
            new FloatField('DISCOUNT_PERCENT'),
            new IntegerField('SORT'),
            new IntegerField('CREATED_BY'),
            new DatetimeField('DATE_CREATE'),
            new IntegerField('MODIFIED_BY'),
            new DatetimeField('TIMESTAMP_X'),
        ];
    }
}
