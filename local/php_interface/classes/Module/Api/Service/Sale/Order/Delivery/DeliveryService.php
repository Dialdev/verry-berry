<?php

namespace Natix\Module\Api\Service\Sale\Order\Delivery;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;
use Natix\Helpers\OrderHelper;
use Natix\Helpers\OrderHelperException;
use Natix\Module\Api\ErrorCodes;
use Natix\Module\Api\Exception\Sale\Order\Delivery\DeliveryServiceException;

/**
 * Сервис для обработки запросов к api, связанных со службами доставок
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class DeliveryService
{
    /**
     * Обработка способа доставки, если он указан
     *
     * @param Order    $order
     * @param Shipment $shipment
     * @param array    $requestParams
     * @param bool     $setOrderProperty
     * @throws DeliveryServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     */
    public function processDelivery(
        Order $order,
        Shipment $shipment,
        array $requestParams,
        bool $setOrderProperty
    ): void {
        $isDeliverySet = false;

        // получаем данные доставок
        $restrictedObjectsList = Manager::getRestrictedObjectsList($shipment);

        foreach ($restrictedObjectsList as $deliveryObj) {
            $deliveryId = (int)$deliveryObj->getId();

            if ((int)$requestParams['DELIVERY_ID'] !== $deliveryId) {
                continue;
            }

            if ($setOrderProperty) {
                if ($this->isPvzDelivery($deliveryObj->getCode())) {
                    $this->setOrderPropertyPvz($order, $requestParams, $deliveryId);
                }
                elseif ($this->isCourierDelivery($deliveryObj->getCode())) {
                    $this->setOrderPropertyCourier($order, $requestParams);
                }
            }

            $setResult = $shipment->setFields([
                'DELIVERY_ID'   => $deliveryId,
                'DELIVERY_NAME' => $this->getName($deliveryObj),
                'CURRENCY'      => $order->getCurrency(),
            ]);

            if (!$setResult->isSuccess()) {
                throw new DeliveryServiceException(implode(', ', $setResult->getErrorMessages()));
            }

            $isDeliverySet = true;

            $shipmentCollection = $order->getShipmentCollection();
            $calculateResult = $shipmentCollection->calculateDelivery();

            if (!$calculateResult->isSuccess()) {
                throw new DeliveryServiceException(implode(', ', $calculateResult->getErrorMessages()));
            }

            break;
        }

        if (!$isDeliverySet) {
            throw new DeliveryServiceException('Указанная в DELIVERY_ID доставка не найдена или недоступна');
        }
    }

    /**
     * Проверяет, что СД является доставкой в ПВЗ или магазин
     *
     * @param string $deliveryCode
     * @return bool
     */
    public function isPvzDelivery(string $deliveryCode): bool
    {
        return strpos($deliveryCode, 'pvz');
    }

    /**
     * Проверяет, что СД является курьерской
     *
     * @param string $deliveryCode
     * @return bool
     */
    public function isCourierDelivery(string $deliveryCode): bool
    {
        return strpos($deliveryCode, 'courier');
    }

    /**
     * Валидирует и сохраняет в заказ свойства, связанные с самовывозом из ПВЗ
     *
     * @param Order $order
     * @param array $requestParams
     * @param int   $deliveryId
     * @throws DeliveryServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private function setOrderPropertyPvz(Order $order, array $requestParams, int $deliveryId): void
    {
        $this->setOrderPropertyCourier($order, $requestParams, ['PROPERTY_DELIVERY_DATE']);
    }

    /**
     * Валидирует и сохраняет в заказ свойства, связанные с курьерской доставкой
     *
     * @param Order $order
     * @param array $requestParams
     * @param array $requiredParams
     * @throws DeliveryServiceException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private function setOrderPropertyCourier(Order $order, array $requestParams, array $requiredParams = []): void
    {
        // ключ это код свойства заказа, значение это имя параметра в $requestParams
        $propertiesMap = [
            'CITY'              => 'PROPERTY_CITY',
            'STREET'            => 'PROPERTY_STREET',
            'HOME'              => 'PROPERTY_HOME',
            'APARTMENT'         => 'PROPERTY_APARTMENT',
            'DISTANCE'          => 'PROPERTY_DISTANCE',
            'EXACT_TIME'        => 'PROPERTY_EXACT_TIME',
            'DELIVERY_DATE'     => 'PROPERTY_DELIVERY_DATE',
            'DELIVERY_INTERVAL' => 'PROPERTY_DELIVERY_INTERVAL',
            'GET_ADDRESS'       => 'PROPERTY_GET_ADDRESS',
            'DELIVERY_COMMENT'  => 'PROPERTY_DELIVERY_COMMENT',
        ];

        $requiredParams = $requiredParams ?: [
            'PROPERTY_CITY',
            'PROPERTY_STREET',
            'PROPERTY_HOME',
            'PROPERTY_DELIVERY_DATE',
        ];

        // Проверим формат даты доставки, который пришел с фронта
        // Просто создадим объект даты, он сам выбросит исключение в случае ошибочного формата
        if (isset($requestParams['PROPERTY_DELIVERY_DATE'])) {
            try {
                new DateTime($requestParams['PROPERTY_DELIVERY_DATE']);
            } catch (\Exception $ex) {
                throw new DeliveryServiceException(
                    sprintf('Не верный формат даты доставки "%s"', $requestParams['PROPERTY_DELIVERY_DATE']),
                    ErrorCodes::DELIVERY_DATE_WRONG_FORMAT
                );
            }
        }

        if (
            isset($requestParams['PROPERTY_DELIVERY_INTERVAL'])
            && !empty($requestParams['PROPERTY_DELIVERY_INTERVAL'])
            && !preg_match('~\d+:\d+( - \d+:\d+)?~', $requestParams['PROPERTY_DELIVERY_INTERVAL'])
        ) {
            throw new DeliveryServiceException(
                sprintf('Неверный формат интервала доставки: %s', $requestParams['PROPERTY_DELIVERY_INTERVAL'])
            );
        }

        // проверка полей на заполненность
        if ($requestParams['PROPERTY_GET_ADDRESS'] !== 'Y') {
            foreach ($requiredParams as $requiredParam) {
                if (empty($requestParams[$requiredParam])) {
                    throw new DeliveryServiceException(sprintf('Не заполнено поле %s', $requiredParam));
                }
            }
        }

        try {
            foreach ($propertiesMap as $orderPropertyCode => $requiredParam) {
                if ($requestParams[$requiredParam]) {
                    OrderHelper::setOrderPropertySingle(
                        $order,
                        $orderPropertyCode,
                        $requestParams[$requiredParam],
                        false
                    );
                }
            }
            
            if ($requestParams['PROPERTY_ADDRESS_COORDINATES']) 
                $this->setOrderPropertyAddressCoordinates($order, $requestParams['PROPERTY_ADDRESS_COORDINATES']);

        } catch (OrderHelperException $e) {
            throw new DeliveryServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Устанавливает интервал доставки в заказ (+3 часа от текущего времени)
     *
     * @param Order $order
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Natix\Helpers\OrderHelperException
     */
    public function setDefaultDeliveryInterval(Order $order): void
    {
        $dateTime = new \DateTimeImmutable();

        $deliveryInterval = sprintf(
            '%s:00 - %s:00',
            $dateTime->modify('+3 hour')->format('H'),
            $dateTime->modify('+4 hour')->format('H')
        );

        OrderHelper::setOrderPropertySingle($order, 'DELIVERY_INTERVAL', $deliveryInterval, false);
    }

    /**
     * Добавляет в заказ данные гео-координат адреса
     *
     * @param Order $order
     * @param       $addressCoordinates
     * @throws DeliveryServiceException
     * @throws OrderHelperException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     */
    private function setOrderPropertyAddressCoordinates(Order $order, $addressCoordinates): void
    {
        if (!is_scalar($addressCoordinates)) {
            throw new DeliveryServiceException(
                'Некорректно заполнено поле PROPERTY_ADDRESS_COORDINATES: должно быть скалярным'
            );
        }

        $addressCoordinates = trim($addressCoordinates);

        if (!preg_match('/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/', $addressCoordinates)) {
            throw new DeliveryServiceException(
                'Некорректно заполнено поле PROPERTY_ADDRESS_COORDINATES: должно быть в формате 55.831332, 37.451402'
            );
        }

        OrderHelper::setOrderPropertySingle($order, 'ADDRESS_COORDINATES', $addressCoordinates, false);
    }

    /**
     * Возвращает имя доставки
     *
     * @param Base $deliveryObj
     * @return string
     */
    protected function getName(Base $deliveryObj): string
    {
        return $deliveryObj->isProfile()
            ? $deliveryObj->getNameWithParent()
            : $deliveryObj->getName();
    }
}
