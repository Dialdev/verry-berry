<?php

namespace Natix\Data\Bitrix\Finder\Sale;

use Natix\Data\Bitrix\Finder\FinderEmptyValueException;
use Natix\Package\MaximasterToolsFinder\PaySystem;
use Natix\Data\Bitrix\Finder\AbstractFinder;

/**
 * Класс содержит шорткарты с ID платежных систем
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PaySystemFinder extends AbstractFinder
{
    /**
     * Оплата с внутреннего счёта
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function inner(): int
    {
        $result = PaySystem::getId('inner');
        $this->checkValue($result, __METHOD__);
        return $result;
    }
    
    /**
     * Оплата наличными при получении
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function cash(): int
    {
        $result = PaySystem::getId('cash');
        $this->checkValue($result, __METHOD__);
        return $result;
    }

    /**
     * Оплата банковской картой (Visa, MasterCard, Maestro)
     *
     * @return int
     * @throws FinderEmptyValueException
     */
    public function cardOnline()
    {
        $result = PaySystem::getId('cloudpayments');
        parent::checkValue($result, __METHOD__);
        return $result;
    }
}
