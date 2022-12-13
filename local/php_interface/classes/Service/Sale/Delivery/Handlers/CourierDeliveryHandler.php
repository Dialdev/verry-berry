<?php

namespace Natix\Service\Sale\Delivery\Handlers;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\Order;
use Natix\Helpers\OrderHelper;

/**
 * Handler для собственной курьерской доставки
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CourierDeliveryHandler extends Base
{
    private $patternDeliveryInterval = '~(\d+):\d+( - (\d+):\d+)?~';

    /** @var string */
    private $hourStart;

    /** @var string */
    private $hourEnd;

    /**
     * @return string
     */
    public static function getClassTitle(): string
    {
        return 'Курьерская доставка Very-berry';
    }

    /**
     * @return string
     */
    public static function getClassDescription(): string
    {
        return 'Собственная курьерская доставка. Стоимость доставки зависит от интервалов доставки и расстояния от МКАД / КАД';
    }

    public static function getClassCode(): string
    {
        return 'manual_courier';
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

    /**
     * {@inheritDoc}
     * @throws ArgumentException
     * @throws \Natix\Helpers\OrderHelperException
     */
    protected function calculateConcrete(Shipment $shipment): CalculationResult
    {
        $result = new CalculationResult();

        $order = $shipment->getCollection()->getOrder();

        $distance = (int)OrderHelper::getOrderPropertyValueByCode($order, 'DISTANCE');

        $deliveryPrice = $this->getDeliveryPriceBySpecialDates($order);

        $deliveryPrice = $this->getDeliveryPrice($order, $deliveryPrice);

        if ($distance > 0)
            $deliveryPrice += $distance * $this->config['MAIN']['PRICE_ZA_MKAD'];

        $result->setDeliveryPrice($deliveryPrice);

        return $result;
    }

    /**
     * Получить цену доставки
     *
     * @param Order $order
     * @param int   $deliveryPrice
     * @return int
     * @throws ArgumentException
     * @throws \Natix\Helpers\OrderHelperException
     */
    protected function getDeliveryPrice(Order $order, int $deliveryPrice = 0): int
    {
        if ($deliveryPrice)
            return $deliveryPrice;

        $requestParams = OrderHelper::getOrderRequestParams();

        //стоимость срочной доставки пока на хардкоде, ибо ну это пиздец
        if ($requestParams['expressDelivery'])
            return 490;

        $exactTime = OrderHelper::getOrderPropertyValueByCode($order, 'EXACT_TIME');

        $deliveryInterval = OrderHelper::getOrderPropertyValueByCode($order, 'DELIVERY_INTERVAL');

        if (preg_match('~^\d{1,2}:\d{1,2}+$~', trim($deliveryInterval)))
            $exactTime = $deliveryInterval;
        else
            $this->prepareDeliveryInterval($deliveryInterval);

        $config = $this->config['MAIN'];

        if (strlen($exactTime) > 0)
            return (float)$config['PRICE_EXACT'];
        elseif ($this->hourStart !== null && $this->hourEnd !== null) {
            $field = sprintf('DI_PRICE_%s_%s', $this->hourStart, $this->hourEnd);

            if (isset($config[$field]))
                return $config[$field];
        }

        return 0;
    }

    /**
     * Получить цену доставки для специальных дат
     *
     * @param Order $order
     * @param int   $deliveryPrice
     * @return int
     * @throws ArgumentException
     * @throws \Natix\Helpers\OrderHelperException
     */
    protected function getDeliveryPriceBySpecialDates(Order $order, int $deliveryPrice = 0): int
    {
        if ($deliveryPrice)
            return $deliveryPrice;

        $deliveryDate = OrderHelper::getOrderPropertyValueByCode($order, 'DELIVERY_DATE');

        $this->prepareDeliveryInterval(OrderHelper::getOrderPropertyValueByCode($order, 'DELIVERY_INTERVAL'));

        if (!$deliveryDate)
            return 0;

        if (self::isDateInSpecialDay($deliveryDate)) {
            $deliveryDate = new \DateTime($deliveryDate);

            $startInterval = (new \DateTime())->setTimestamp($deliveryDate->getTimestamp())->setTime($this->hourStart, 0, 0);

            $endInterval = (new \DateTime())->setTimestamp($deliveryDate->getTimestamp())->setTime($this->hourEnd, 0, 0);

            $interval = $startInterval->diff($endInterval);

            if ($interval->invert or $interval->h <= 3)
                return 400; //цена доставки в праздничные дни
            else
                return 0;
        }

        return 0;
    }


    /**
     * Получить специальные даты с ценой
     *
     * @return array
     * @throws \Exception
     */
    public static function getSpecialDates(): array
    {
        $year = date('Y');

        return [
            new \DateTime("$year-02-14"),

            new \DateTime("$year-03-04"),
            new \DateTime("$year-03-05"),
            new \DateTime("$year-03-06"),
            new \DateTime("$year-03-07"),
            new \DateTime("$year-03-08"),
            new \DateTime("$year-03-09"),

            (new \DateTime)->setTimestamp(strtotime("last sunday of November $year")),

            new \DateTime("$year-12-28"),
            new \DateTime("$year-12-29"),
            new \DateTime("$year-12-30"),
            new \DateTime("$year-12-31"),
        ];
    }

    /**
     * Попадает ли переданная дата в праздничный день
     *
     * @param string $date
     * @return bool
     * @throws \Exception
     */
    public static function isDateInSpecialDay(string $date): bool
    {
        $date = new \DateTime($date);

        $specialDates = self::getSpecialDates();

        $format = 'm-d';

        foreach ($specialDates as $specialDate) {
            if ($date->format($format) == $specialDate->format($format))
                return true;
        }

        return false;
    }

    /**
     * Проверяет переданный интервал доставки на соответствие шаблону.
     * В случае несоответствия кидает исключение.
     *
     * @param string $deliveryInterval
     * @throws ArgumentException
     */
    private function prepareDeliveryInterval($deliveryInterval)
    {
        if (!$deliveryInterval) {
            throw new ArgumentException('Не передан интервал доставки');
        }

        if (!preg_match($this->patternDeliveryInterval, $deliveryInterval, $matches)) {
            throw new ArgumentException(
                sprintf(
                    'Переданный интервал доставки "%s" не соответствует шаблону',
                    $deliveryInterval
                )
            );
        }

        $this->hourStart = $matches[1];
        $this->hourEnd = $matches[3];
    }

    protected function getConfigStructure()
    {
        return [
            'MAIN' => [
                'TITLE'       => 'Настройка обработчика',
                'DESCRIPTION' => 'Настройка обработчика',
                'ITEMS'       => [
                    'PRICE_ZA_MKAD'  => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки за 1 км от МКАД, руб.',
                    ],
                    'PRICE_EXACT'    => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки к точному времени',
                    ],
                    'DI_PRICE_00_01' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 00:00 - 01:00, руб.',
                    ],
                    'DI_PRICE_01_02' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 01:00 - 02:00, руб.',
                    ],
                    'DI_PRICE_02_03' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 02:00 - 03:00, руб.',
                    ],
                    'DI_PRICE_03_04' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 03:00 - 04:00, руб.',
                    ],
                    'DI_PRICE_04_05' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 04:00 - 05:00, руб.',
                    ],
                    'DI_PRICE_05_06' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 05:00 - 06:00, руб.',
                    ],
                    'DI_PRICE_06_07' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 06:00 - 07:00, руб.',
                    ],
                    'DI_PRICE_07_08' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 07:00 - 08:00, руб.',
                    ],
                    'DI_PRICE_08_09' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 08:00 - 09:00, руб.',
                    ],
                    'DI_PRICE_09_10' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 09:00 - 10:00, руб.',
                    ],
                    'DI_PRICE_10_11' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 10:00 - 11:00, руб.',
                    ],
                    'DI_PRICE_11_12' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 11:00 - 12:00, руб.',
                    ],
                    'DI_PRICE_12_13' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 12:00 - 13:00, руб.',
                    ],
                    'DI_PRICE_13_14' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 13:00 - 14:00, руб.',
                    ],
                    'DI_PRICE_14_15' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 14:00 - 15:00, руб.',
                    ],
                    'DI_PRICE_15_16' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 15:00 - 16:00, руб.',
                    ],
                    'DI_PRICE_16_17' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 16:00 - 17:00, руб.',
                    ],
                    'DI_PRICE_17_18' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 17:00 - 18:00, руб.',
                    ],
                    'DI_PRICE_18_19' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 18:00 - 19:00, руб.',
                    ],
                    'DI_PRICE_19_20' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 19:00 - 20:00, руб.',
                    ],
                    'DI_PRICE_20_21' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 20:00 - 21:00, руб.',
                    ],
                    'DI_PRICE_21_22' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 21:00 - 22:00, руб.',
                    ],
                    'DI_PRICE_22_23' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 22:00 - 23:00, руб.',
                    ],
                    'DI_PRICE_23_00' => [
                        'TYPE' => 'NUMBER',
                        'NAME' => 'Стоимость доставки 23:00 - 00:00, руб.',
                    ],
                ],
            ],
        ];
    }
}
