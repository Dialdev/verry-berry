<?php

namespace Natix\Entity\Orm\Iblock;

use Bitrix\Iblock\IblockTable;
use \Bitrix\Iblock\PropertyTable;
use \Bitrix\Main;
use \Bitrix\Main\ArgumentException;
use \Bitrix\Main\DB\Result;
use Bitrix\Main\DB\SqlExpression;
use \Bitrix\Main\Entity;
use \CPHPCache;

/**
 * ORM для ElementTable дающий возможность работать со свойствами
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ElementTable extends \Bitrix\Iblock\ElementTable
{
    /**
     * @var int
     */
    protected static $iblockId;

    /**
     * @var array
     */
    protected static $metadata;

    /**
     * @var bool
     */
    public static $cacheMetadata = true;

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        return parent::getTableName();
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     * @throws ArgumentException
     * @throws Main\LoaderException
     */
    public static function getMap(): array
    {
        static::$metadata = static::getMetadata();

        $map = parent::getMap();

        $singleEntity = SinglePropertyElementTable::createEntity(static::$iblockId);

        $map[] = new Entity\ReferenceField(
            'PROPERTY',
            $singleEntity->getDataClass(),
            ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
            ['join_type' => 'INNER']
        );

        $multiEntity = MultiplePropertyElementTable::createEntity(static::$iblockId);

        foreach (static::$metadata['props'] as $arProp) {
            if ($arProp['MULTIPLE'] === 'Y') {
                $map[] = new Entity\ReferenceField(
                    sprintf('PROPERTY_%s_ENTITY', $arProp['CODE']),
                    $multiEntity->getDataClass(),
                    [
                        '=this.ID'  => 'ref.IBLOCK_ELEMENT_ID',
                        '=ref.IBLOCK_PROPERTY_ID' => new SqlExpression('?i', $arProp['ID'])
                    ],
                    ['join_type' => 'LEFT']
                );

                $map[] = new Entity\ExpressionField(
                    sprintf('PROPERTY_%s', $arProp['CODE']),
                    '%s',
                    sprintf('PROPERTY_%s_ENTITY.VALUE', $arProp['CODE'])
                );

                $map[] = new Entity\ExpressionField(
                    sprintf('PROPERTY_%s_DESCRIPTION', $arProp['CODE']),
                    '%s',
                    sprintf('PROPERTY_%s_ENTITY.DESCRIPTION', $arProp['CODE'])
                );
            } else {
                $map[] = new Entity\ExpressionField(
                    sprintf('PROPERTY_%s', $arProp['CODE']),
                    '%s',
                    sprintf('PROPERTY.%s', $arProp['CODE'])
                );

                $map[] = new Entity\ExpressionField(
                    sprintf('PROPERTY_%s_DESCRIPTION', $arProp['CODE']),
                    '%s',
                    sprintf('PROPERTY.%s_DESCRIPTION', $arProp['CODE'])
                );
            }
        }


        return $map;
    }

    /**
     * Fetches iblock metadata for further using. Uses cache.
     * Cache can be disabled with flag self::#cacheMetadata = false.
     * @param int|null $iblockId
     * @return array
     * @throws Main\ArgumentException
     * @throws Main\LoaderException
     */
    public static function getMetadata($iblockId = null): array
    {
        if ($iblockId === null) {
            $iblockId = static::$iblockId;
        }

        $result = [];
        $obCache = new CPHPCache;
        $cacheDir = '/' . $iblockId;

        if (static::$cacheMetadata && $obCache->InitCache(3600, 'iblockOrm', $cacheDir)) {
            $result = $obCache->GetVars();
        } else {
            $result['iblock'] = IblockTable::getRowById($iblockId);

            $result['props'] = [];

            $rs = PropertyTable::getList([
                'filter' => [
                    '=IBLOCK_ID' => $iblockId,
                ],
            ]);

            while ($arProp = $rs->fetch()) {
                $result['props'][$arProp['CODE']] = $arProp;
            }

            if (static::$cacheMetadata) {
                $obCache->StartDataCache();
                $obCache->EndDataCache($result);
            }
        }

        return $result;
    }

    /**
     * Wrapper for DataManager::getList() with page navigation support
     * @param array $parameters
     * @return Result
     * @throws ArgumentException
     */
    public static function getList(array $parameters): Result
    {
        $parameters['filter']['=IBLOCK_ID'] = static::$iblockId;
        $rs = parent::getList($parameters);
        return $rs;
    }

    /**
     * Возвращает ИД ИБ
     * @return int
     */
    public static function getIBlockId()
    {
        return static::$iblockId;
    }
}
