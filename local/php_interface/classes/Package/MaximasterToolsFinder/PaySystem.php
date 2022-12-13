<?php

namespace Natix\Package\MaximasterToolsFinder;

use Bitrix\Sale\Internals\PaySystemActionTable;
use Maximaster\Tools\Finder\AbstractFinder;

/**
 * Finder для платежных систем. Ищет платежные системы по их символьному коду, кеширует и возвращает результат из кеша
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PaySystem extends AbstractFinder
{
    protected function requireModules(): array
    {
        return ['sale'];
    }

    protected function getAdditionalCachePath(): string
    {
        return '/paymentService_profile';
    }

    /**
     * Получает параметры Платежной системы по ее коду
     * 
     * @param string $code
     * 
     * @return \Bitrix\Main\Entity\Query
     */
    protected function query($code)
    {
        $q = PaySystemActionTable::query()
            ->setSelect(['CODE', 'ID']);
        $this->setQueryMetadata('CODE', $code);
        return $q;
    }

    /**
     * @param string $code
     *
     * @return array
     */
    public static function get($code)
    {
        return parent::get($code);
    }

    /**
     * @param string $code
     *
     * @return int
     */
    public static function getId($code): int
    {
        return (int)self::get($code)['ID'];
    }
}
