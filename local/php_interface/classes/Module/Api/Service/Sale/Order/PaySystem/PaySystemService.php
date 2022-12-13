<?php

namespace Natix\Module\Api\Service\Sale\Order\PaySystem;

use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Manager;
use Natix\Data\Bitrix\Finder\Sale\PaySystemFinder;
use Natix\Module\Api\Exception\Sale\Order\PaySystem\PaySystemServiceException;
use Natix\Module\Api\Service\Sale\Order\Bonus\BonusService;

/**
 * Сервис для обработки запросов к api, связанных с платёжными системами
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PaySystemService
{
    /** @var BonusService */
    private $bonusService;
    
    /** @var PaySystemFinder */
    private $paySystemFinder;
    
    public function __construct(BonusService $bonusService, PaySystemFinder $paySystemFinder)
    {
        $this->bonusService = $bonusService;
        $this->paySystemFinder = $paySystemFinder;
    }

    /**
     * Устанавливает системы оплаты в заказ
     *
     * @param Order $order
     * @param array $requestParams
     *
     * @throws PaySystemServiceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public function setPaySystems(Order $order, array $requestParams): void
    {
        $userId = $order->getUserId();
        $price = $order->getPrice();
        
        // устанавливаем оплату бонусами, если она передана
        $currentPayBonus = isset($requestParams['BONUS_PAY']) ? (int) $requestParams['BONUS_PAY'] : 0;

        if ($requestParams['USE_BONUSPAY'] === 'Y' && $currentPayBonus > 0 && $userId > 0) {
            $currentBonuses = $this->bonusService->getUserBonuses($userId);
            $currentBudget = (int)$currentBonuses['CURRENT_BUDGET'];
            
            if ($currentPayBonus > $currentBudget) {
                throw new PaySystemServiceException(
                    'Указанное количество бонусов превышает доступное количество бонусов'
                );
            }

            // уменьшаем сумму оплаты основным способом на сумму частичной оплаты бонусами
            $price -= $currentPayBonus;

            $this->setBonusPaySystem($order, $currentPayBonus);
        }
        
        // Если установлена система оплаты, то добавляем основной способ оплаты
        if (!empty($requestParams['PAY_SYSTEM_ID'])) {
            // устанавливаем основной способ оплаты
            $this->setPaySystem(
                $order,
                $requestParams,
                (int)$requestParams['PAY_SYSTEM_ID'],
                $price
            );
        }
    }

    /**
     * Находит в заказе оплату бонусами и проводит её, если она ещё не оплачена
     * 
     * @param Order $order
     * @param array $requestParams
     *
     * @throws PaySystemServiceException
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    public function setBonusPaymentPaid(Order $order, array $requestParams): void
    {
        if ($requestParams['USE_BONUSPAY'] !== 'Y') {
            return;
        }
        
        if ((int)$order->getUserId() <= 0) {
            throw new PaySystemServiceException('В заказе не установлен идентификатор пользователя');
        }

        $bonusPaymentId  = $this->paySystemFinder->inner();
        $bonusPayment = null;
        
        /** @var Payment $payment */
        foreach ($order->getPaymentCollection()->getIterator() as $payment) {
            if (
                (int)$payment->getPaymentSystemId() === $bonusPaymentId
                && !$payment->isPaid()
            ) {
                $bonusPayment = $payment;
            }
        }
        if ($bonusPayment === null) {
            throw new PaySystemServiceException('В заказе нет способа оплаты бонусами');
        }

        $currentPayBonus = isset($requestParams['BONUS_PAY']) ? (int) $requestParams['BONUS_PAY'] : 0;
        $currentBonuses = $this->bonusService->getUserBonuses($order->getUserId());
        $currentBudget = (int)$currentBonuses['CURRENT_BUDGET'];

        if ($currentPayBonus > $currentBudget) {
            throw new PaySystemServiceException(
                'Указанное количество бонусов превышает доступное количество бонусов'
            );
        }
        
        if ($currentPayBonus > $order->getPrice()) {
            throw new PaySystemServiceException(
                'Переданная сумма оплаты бонусами превышает сумму заказа'
            );
        }
        
        /** @var Payment $currentPay */
        $currentPay = $order->getPaymentCollection()->current();
        $newSum = $order->getPrice() - $order->getSumPaid() - $currentPayBonus;
        
        if ($newSum !== 0.0) {
            $currentPay->setField('SUM', $newSum);
        }

        $bonusPayment->setFields([
            'SUM' => $currentPayBonus,
            'CURRENCY' => $order->getCurrency()
        ]);

        $bonusPayment->setPaid('Y');
        $order->save();
        //$bonusPayment->save();
    }

    /**
     * Устанавливает основную систему оплаты
     *
     * @param Order $order
     * @param array $requestParams
     * @param int $paySystemId
     * @param float $price
     *
     * @throws PaySystemServiceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     */
    private function setPaySystem(Order $order, array $requestParams, int $paySystemId, float $price): void
    {
        $paymentItem = $order->getPaymentCollection()->createItem();
        $setResult = $paymentItem->setFields([
            'SUM' => $price,
            'CURRENCY' => $order->getCurrency(),
        ]);
        if (!$setResult->isSuccess()) {
            throw new PaySystemServiceException(implode(', ', $setResult->getErrorMessages()));
        }

        $arPaySystemServices = Manager::getListWithRestrictions($paymentItem);

        $isPaySystem = false;
        foreach ($arPaySystemServices as $arPaySystem) {
            if ($paySystemId === (int)$arPaySystem['ID']) {
                $setResult = $paymentItem->setFields([
                    'PAY_SYSTEM_ID' => $arPaySystem['ID'],
                    'PAY_SYSTEM_NAME' => $arPaySystem['NAME'],
                ]);
                if (!$setResult->isSuccess()) {
                    throw new PaySystemServiceException(implode(', ', $setResult->getErrorMessages()));
                }

                $isPaySystem = true;
                break;
            }
        }

        if (!$isPaySystem) {
            throw new PaySystemServiceException(
                sprintf('Платёжная система PAY_SYSTEM_ID=%s не найдена', $requestParams['PAY_SYSTEM_ID'])
            );
        }
    }

    /**
     * Устанавливает частичную оплату баллами
     *
     * @param Order $order
     * @param float $price
     *
     * @throws PaySystemServiceException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Natix\Data\Bitrix\Finder\FinderEmptyValueException
     */
    private function setBonusPaySystem(Order $order, float $price): void
    {
        $paymentItem = $order->getPaymentCollection()->createItem(
            Manager::getObjectById($this->paySystemFinder->inner())
        );
        $setResult = $paymentItem->setFields([
            'SUM' => $price,
            'CURRENCY' => $order->getCurrency(),
        ]);
        if (!$setResult->isSuccess()) {
            throw new PaySystemServiceException(implode(', ', $setResult->getErrorMessages()));
        }
    }
}
