<?php

namespace Natix\Component;
use Bitrix\Catalog\StoreTable;
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
        $this->arParams['CACHE_TYPE'] = 'Y';
        $this->arParams['CACHE_TIME'] = 3600;
        $this->arParams['CACHE_GROUPS'] = 'N';

        $this->location = LocationHelper::getLocationCode();
        $this->addCacheAdditionalId($this->location);
    }

    protected function executeMain()
    {

        $params = [
            'LOCATION' => $this->location,
            'PERSON_TYPE_ID' => OrderHelper::PERSON_CUSTOMER,
        ];

        $nowLocId = $this->deliveryGroupService->getDeliveryByGroup($params)['pvz']['ROWS'][0]["TITLE"];



        $res= StoreTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '=ACTIVE' => 'Y',
            ])
            ->exec();


        while ($vars = $res->fetch()) {
            $arShops[] = $vars;
        }


        foreach ($arShops as $key => $shop) {


            if ($shop["TITLE"] == $nowLocId) {

                $shop["SELECTED"]="Y";
                $this->arResult['shops'][0] = $shop;
                unset($arShops[$key]);
                break;
            }
        }


        foreach ($arShops as $key => $shop) {
            $this->arResult['shops'][] = $shop;
        }
        //print_r( $this->arResult['shops']);


    }
}
