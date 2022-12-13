<?php

namespace Natix\Helpers;

use Bitrix\Main\Diag;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Sale\Order;
use Bitrix\Sale\PropertyValue;
use Psr\Log\LoggerInterface;

/**
 * Хелпер для заказов
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class OrderHelper
{
    /**
     * id типа плательщика "Физическое лицо"
     */
    const PERSON_CUSTOMER = 1;

    /** Выполнен */
    const STATUS_DONE = 'F';

    /** Оплачен */
    const STATUS_PAID = 'P';

    protected static array $orderRequestParams = [];

    /**
     * Возвращает значение свойства заказа
     *
     * @param Order $order
     * @param       $propertyCode
     * @param bool  $silenceMode
     * @return bool|null|string
     * @throws OrderHelperException
     */
    public static function getOrderPropertyValueByCode(Order $order, $propertyCode, $silenceMode = false)
    {
        $result = null;

        try {
            $property = self::getOrderPropertyObjectByCode($order, $propertyCode, $silenceMode);

            if ($property instanceof PropertyValue) {
                $result = $property->getValue();
            }
        } catch (OrderHelperException $e) {
            if ($silenceMode === false) {
                throw $e;
            }

            return false;
        }

        return $result;
    }

    /**
     * Возвращает значение свойства заказа в виде его объекта на основе кода свойства
     *
     * @param Order $order
     * @param       $propertyCode
     * @param bool  $silenceMode
     * @return PropertyValue|null
     * @throws OrderHelperException
     */
    public static function getOrderPropertyObjectByCode(Order $order, $propertyCode, $silenceMode = false)
    {
        $result = null;

        try {
            if (!($propertyCollection = $order->getPropertyCollection())) {
                throw new OrderHelperException(
                    sprintf(
                        'PropertyValueCollection не объект'
                    )
                );
            }

            $findProperty = false;

            /** @var PropertyValue $propertyValue */
            foreach ($propertyCollection as $propertyValue) {
                if (
                    ($property = $propertyValue->getProperty())
                    && $property['CODE'] === $propertyCode
                ) {
                    $findProperty = true;

                    $result = $propertyValue;

                    break;
                }
            }

            if ($findProperty === false) {
                throw new OrderHelperException(
                    sprintf(
                        'Свойство с кодом "%s" не найдено в заказе "%s"',
                        $propertyCode,
                        $order->getId()
                    )
                );
            }
        } catch (OrderHelperException $e) {
            if ($silenceMode === false) {
                throw $e;
            }
        }

        return $result;
    }

    /**
     * @param int|Order $order
     * @param string    $code
     * @param string    $value
     * @param bool      $silenceMode
     * @return bool
     * @throws OrderHelperException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function setOrderPropertySingle($order, $code, $value, $silenceMode = true)
    {
        $result = false;

        static $includeSaleModule = null;

        if ($includeSaleModule === null) {
            $includeSaleModule = Loader::includeModule('sale');
        }

        if (is_object($order) && ($order instanceof Order)) {
            $orderId = $order->getId();
            $orderObjectMode = true;
        }
        else {
            $orderId = $order;
            $orderObjectMode = false;
        }

        /** @var LoggerInterface $logger */
        $logger = \Natix::$container->get(LoggerInterface::class);

        $loggerContext = [
            'order_id'  => $orderId,
            'func_name' => __METHOD__,
        ];

        $backTrace = Diag\Helper::getBackTrace(5, ~DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

        try {
            if (!$includeSaleModule) {
                throw new OrderHelperException('Ошибка подключения модуля sale');
            }

            if ($orderObjectMode === false) {
                if ((int)$orderId <= 0) {
                    throw new OrderHelperException('Неверный номер заказа');
                }

                if (!($order = Order::load($orderId))) {
                    throw new OrderHelperException(sprintf('Заказ "%s" не найден', $orderId));
                }
            }

            if ($propertyCollection = $order->getPropertyCollection()) {
                $findProperty = false;

                $oldPropertyValue = null;

                /** @var \Bitrix\Sale\PropertyValue $propertyItem */
                foreach ($propertyCollection as $propertyItem) {
                    if ($propertyItem->getField('CODE') === $code) {
                        $findProperty = true;

                        $oldPropertyValue = $propertyItem->getValue();

                        $resultSetField = $propertyItem->setField('VALUE', $value);

                        if (!$resultSetField->isSuccess()) {
                            throw new OrderHelperException(
                                sprintf(
                                    'Ошибка установки значения "%s" свойства "%s" заказа "%s": %s',
                                    $value,
                                    $code,
                                    $orderId,
                                    implode(', ', $resultSetField->getErrorMessages())
                                )
                            );
                        }

                        if ($orderObjectMode === false) {
                            $resultOrderSave = $order->save();

                            if (!$resultOrderSave->isSuccess()) {
                                throw new OrderHelperException(
                                    sprintf(
                                        'Ошибка сохранения заказа "%s" после установки значения "%s" свойства "%s": %s',
                                        $orderId,
                                        $value,
                                        $code,
                                        implode(', ', $resultOrderSave->getErrorMessages())
                                    )
                                );
                            }
                        }

                        break;
                    }
                }

                if ($findProperty === false) {
                    throw new OrderHelperException(
                        sprintf(
                            'Свойство с кодом "%s" не найдено в заказе "%s"',
                            $code,
                            $orderId
                        )
                    );
                }

                $logger->info(
                    sprintf(
                        'Значениe "%s" (старое "%s") свойства "%s" заказа "%s" успешно сохранено. Backtrace: %s',
                        $value,
                        $oldPropertyValue,
                        $code,
                        $orderId,
                        Json::encode($backTrace)
                    ),
                    $loggerContext
                );

                $result = true;
            }
        } catch (OrderHelperException $e) {
            $logger->warning(
                sprintf(
                    '%s. Backtrace: "%s"',
                    $e->getMessage(),
                    Json::encode($backTrace)
                ),
                $loggerContext
            );

            if ($silenceMode === false) {
                throw $e;
            }
        }

        return $result;
    }


    /**
     * Устновить параметра заказа из http-запроса
     * 
     * @param array $requestParams
     */
    public static function setOrderRequestParams(array $requestParams): void
    {
        self::$orderRequestParams = $requestParams;
    }

    /**
     * Получить параметра заказа из http-запроса
     * 
     * @return array
     */
    public static function getOrderRequestParams(): array
    {
        return self::$orderRequestParams;
    }
}
