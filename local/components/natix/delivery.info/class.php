<?php

namespace Natix\Component;

use Natix\Helpers\LocationHelper;
use Natix\Helpers\OrderHelper;
use Natix\Module\Api\Service\Sale\Order\Delivery\DeliveryGroupService;
use Psr\Log\LoggerInterface;

/**
 * Компонент показа информации о доставке
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class DeliveryInfo extends CommonComponent
{
    /** @var array */
    protected $needModules = [
        'sale',
    ];

    /** @var string */
    private $location;

    /** @var DeliveryGroupService */
    private $deliveryGroupService;

    /** @var LoggerInterface */
    private $logger;

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->deliveryGroupService = $this->getContainer()->get(DeliveryGroupService::class);
        $this->logger = \Natix::$container->get(LoggerInterface::class);
    }

    protected function configurate()
    {
        $this->arParams['CACHE_TYPE'] = 'Y';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';

        $this->location = LocationHelper::getLocationCode();
        $this->addCacheAdditionalId($this->location);
    }
    
    protected function executeMain()
    {
        try {
            $params = [
                'LOCATION' => $this->location,
                'PERSON_TYPE_ID' => OrderHelper::PERSON_CUSTOMER,
            ];
            $this->arResult['delivery_by_group'] = $this->deliveryGroupService->getDeliveryByGroup($params);

        } catch (\Exception $exception) {
            $this->logger->error(sprintf(
                'Ошибка в компоненте показа информации о доставке: %s',
                $exception->getMessage()
            ));
            return false;
        }
    }
}
