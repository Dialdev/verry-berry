<?php

namespace Natix\Component;

use Natix\Helpers\LocationHelper;
use Natix\Helpers\OrderHelper;
use Natix\Module\Api\Service\Sale\Order\Delivery\DeliveryGroupService;
use Psr\Log\LoggerInterface;

/**
 * Компонент страницы "Самовывоз".
 * Вывод магазина зависит от текущего местоположения пользователя.
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ShopDetail extends CommonComponent
{
    protected $needModules = [
        'sale',
    ];
    
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
        $this->arParams['CACHE_TYPE'] = 'N';
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
            $deliveryByGroup = $this->deliveryGroupService->getDeliveryByGroup($params);
            $this->arResult['shop'] = $deliveryByGroup['pvz']['ROWS']
                ? reset($deliveryByGroup['pvz']['ROWS'])
                : null;


        } catch (\Exception $exception) {
            $this->logger->error(sprintf(
                'Ошибка в компоненте страницы самовывоза: %s',
                $exception->getMessage()
            ));
            return false;
        }
    }
}
