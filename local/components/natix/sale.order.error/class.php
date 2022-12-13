<?php

namespace Natix\Component;

use Bitrix\Sale\Order;


class SaleOrderCancel extends CommonComponent
{
    /** @var array */
    protected $needModules = [
        'sale',
    ];

    /** @var bool */
    protected $cacheTemplate = false;

    /** @var Order */
    private $order;

    protected function configurate()
    {
        $this->arParams['ORDER_ID'] = (int)$this->arParams['ORDER_ID'];

        $request = $this->request->toArray();

        $this->arParams['ERROR'] = $request['ERROR'];

        $this->addCacheAdditionalId('cancel_order_'.$this->arParams['ORDER_ID']);
    }

    protected function executeMain()
    {
        if (!$this->arParams['ORDER_ID'])
            throw new \Exception('Не передан номер заказа');

        $this->order = Order::load((int)$this->arParams['ORDER_ID']);

        if ($this->order === null)
            throw new \Exception(sprintf('Заказ №%s не существует', $this->arParams['ORDER_ID']));

        $this->arResult = $this->arParams;
    }
}
