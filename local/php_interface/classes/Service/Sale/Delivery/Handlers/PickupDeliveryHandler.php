<?php

namespace Natix\Service\Sale\Delivery\Handlers;

use Bitrix\Sale\Delivery\Services\Configurable;

/**
 * Handler для доставки самовывозом
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PickupDeliveryHandler extends Configurable
{
    /**
     * @return string
     */
    public static function getClassTitle(): string
    {
        return 'Самовывоз Very-berry';
    }

    public static function getClassCode(): string
    {
        return 'manual_pvz';
    }

    /**
     * @param array $fields
     * @return array
     */
    public function prepareFieldsForUsing(array $fields): array
    {
        $fields['CODE'] = self::getClassCode();
        return parent::prepareFieldsForUsing($fields);
    }

    /**
     * @param array $fields
     * @return array
     * @throws \Bitrix\Main\SystemException
     */
    public function prepareFieldsForSaving(array $fields): array
    {
        $fields['CODE'] = self::getClassCode();
        return parent::prepareFieldsForSaving($fields);
    }
}
