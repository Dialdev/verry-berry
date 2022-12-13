<?php

namespace Natix\Service\EntityProcessingQueue\Entity\Orm;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Bitrix\Main\Web\Json;

/**
 * Таблица для очереди на обработку различных сущностей
 * Например, при изменении статуса заказа на выполнен, можно поставить этот заказ в очередь на обработку
 * а затем по крону обработать такие заказы и, например, разослать уведомления пользователям
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class NatixEntityProcessingQueueTable extends Entity\DataManager
{
    /**
     * Вернет название таблицы в базе
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'natix_entity_processing_queue';
    }

    /**
     * Вернет массив параметров сущности
     *
     * @return array
     * @throws \Exception
     */
    public static function getMap()
    {
        return [
            'ID' => new Entity\IntegerField('ID', [
                'primary' => true,
                'autoincrement' => true,
            ]),
            // дата постановки в очередь
            'ENQUEUE_DATE' => new Entity\DatetimeField('ENQUEUE_DATE', [
                'required' => false,
                'default_value' => new Type\DateTime(),
            ]),
            // тип сущности - заказ, товар...
            'ENTITY_TYPE' => new Entity\StringField('ENTITY_TYPE', [
                'required' => true,
            ]),
            // ид сущности
            'ENTITY_ID' => new Entity\StringField('ENTITY_ID', [
                'required' => true,
            ]),
            // запись обрабатывается
            'IS_PROCESSING' => new Entity\BooleanField('IS_PROCESSING', [
                'required' => false,
                'default_value' => false,
            ]),
            // действие, которое необходимо выполнить над сущностью
            'ACTION' => new Entity\StringField('ACTION', [
                'required' => true,
            ]),
            // сериализованные данные сущности
            'ENTITY_DATA' => new Entity\TextField('ENTITY_DATA', [
                'required' => false,
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return Json::encode($value);
                        },
                    ];
                },
                'fetch_data_modification' => function () {
                    return [
                        function ($value) {
                            return Json::decode($value);
                        },
                    ];
                },
            ]),
            // При обработке возникла ошибка
            'IS_ERROR' => new Entity\BooleanField('IS_ERROR', [
                'required'      => false,
                'default_value' => false,
            ]),
            // Текст ошибки
            'ERROR_TEXT' => new Entity\TextField('ERROR_TEXT', [
                'required' => false,
            ]),
        ];
    }
}
