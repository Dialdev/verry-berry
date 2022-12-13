<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Application,
    Bitrix\Sale\Order,
    Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\SystemException;

Loader::includeModule('iblock');


class orderlist extends CBitrixComponent
{

    /**
     * @override
     */
    public function onIncludeComponentLang()
    {

        parent::onIncludeComponentLang();
        $this->includeComponentLang(basename(__FILE__));
    }

    /**
     * @param $params
     * @override
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        $params = parent::onPrepareComponentParams($params);

        if (!isset($params['CACHE_TIME'])) {
            $params['CACHE_TIME'] = 86400;
        }
        $params['CACHE_GROUPS'] = ($params['CACHE_GROUPS'] == 'Y');

        return $params;
    }

    protected function extractDataFromCache()
    {
        if ($this->arParams['CACHE_TYPE'] == 'N') {
            return false;
        }

        $additional = $this->arParams;
        $additional[] = Application::getInstance()->getContext()->getRequest()->getQueryList();

        if ($this->arParams['CACHE_GROUPS']) {
            global $USER;
            $additional[] = $USER->GetGroups();
        }
        if (check_bitrix_sessid()) {
            return false;
        }

        return !($this->StartResultCache(false, $additional));
    }

    protected function putDataToCache()
    {
        $this->endResultCache();
    }

    protected function abortDataCache()
    {
        $this->AbortResultCache();
    }

    private function initResult()
    {
        global $USER;
        if($USER->IsAuthorized()){
            $arStatusClasses = [
              'F' => 'cart-lk__label_archiv',
            ];
            $arPayedStatus = [
              'Y' =>['NAME'=>Loc::getMessage('PAYED'),'CLASS' =>'cart-lk__label_success'],
              'N' =>['NAME'=>Loc::getMessage('NO_PAYED'), 'CLASS' =>'cart-lk__label_nopay'],
            ];

            $statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList(array(

                'order' => array('STATUS.SORT'=>'ASC'),
                'filter' => array('STATUS.TYPE'=>'O','LID'=>LANGUAGE_ID),
                'select' => array('STATUS_ID','NAME','DESCRIPTION'),

            ));

            while ($status = $statusResult->fetch())
            {
                $status['CLASS'] = $arStatusClasses[$status['STATUS_ID']]?:'cart-lk__label_new';
               $arStatuses[$status['STATUS_ID']] = $status;
            }

            $parameters = [
                'select' => ['*','PROPERTY_*'],
                'filter' => [
                    'USER_ID' => $USER->GetID(),
                ],
                'order' => ['DATE_INSERT' => 'DESC'],
            ];
            $res = Order::getList($parameters);
            
            /** @var \Natix\Data\Bitrix\Finder\Sale\PaySystemFinder $paySystemFinder */
            $paySystemFinder = \Natix::$container->get(\Natix\Data\Bitrix\Finder\Sale\PaySystemFinder::class);
            
            while ($row = $res->fetch()) {
                $order = Order::load($row['ID']);
                
                $bonuses = 0;
                /** @var \Bitrix\Sale\Payment $payment */
                foreach ($order->getPaymentCollection()->getIterator() as $payment) {
                    if ($payment->getPaymentSystemId() == $paySystemFinder->inner()) {
                        $bonuses = $payment->getSum();
                    }
                }
                
                $deliveryPrice = $order->getDeliveryPrice();
                $orderPrice = $order->getPrice() - $deliveryPrice - $bonuses;
                
                $row['STATUS'] = $arStatuses[$row['STATUS_ID']];
                $row['PAY_STATUS'] = $arPayedStatus[$row['PAYED']];
                $row['DATE_INSERT_FORMATTED'] = FormatDate('d.m.Y',$row['DATE_INSERT']);
                $row['PRICE_FORMATTED'] = CCurrencyLang::CurrencyFormat($order->getPrice(), 'RUB');
                $row['PRICE_DELIVERY_FORMATTED'] = CCurrencyLang::CurrencyFormat($deliveryPrice, 'RUB');
                $row['ITEM_PRICE_FORMATTED'] = CCurrencyLang::CurrencyFormat($orderPrice, 'RUB');
                $row['BONUSES'] = $bonuses;
                $row['BONUSES_FORMATTED'] = \CCurrencyLang::CurrencyFormat(
                    $bonuses,
                    $order->getCurrency()
                );
                
                $this->arResult['ITEMS'][$row['ID']] = $row;
            }
            if($this->arResult['ITEMS']){
                $dbProps = \Bitrix\Sale\PropertyValueCollection::getList([
                    'select' => ['*'],
                    'filter' => [
                        '=ORDER_ID' => array_keys($this->arResult['ITEMS']),
                    ],
                ]);
                while ($item = $dbProps->fetch())
                {
                    if($item['VALUE']=='Y')$item['VALUE'] = 'Да';
                        elseif($item['VALUE']=='N')$item['VALUE'] = 'Нет';
                    $this->arResult['ITEMS'][$item['ORDER_ID']]['PROPERTIES'][$item['CODE']] = $item;
                }

                $dbRes = \Bitrix\Sale\PaymentCollection::getList([
                    'select' => ['*'],
                    'filter' => [
                        '=ORDER_ID' => array_keys($this->arResult['ITEMS']),
                    ],
                ]);

                while ($item = $dbRes->fetch())
                {
                    $this->arResult['ITEMS'][$item['ORDER_ID']]['PAYMENT_INFO'] = $item;
                }
                $dbCoupon = \Bitrix\Sale\Internals\OrderCouponsTable ::getList([
                    'select' => ['*'],
                    'filter' => [
                        '=ORDER_ID' => array_keys($this->arResult['ITEMS']),
                    ],
                ]);

                while ($item = $dbCoupon->fetch())
                {
                    $this->arResult['ITEMS'][$item['ORDER_ID']]['COUPON'] = $item;
                }

                foreach ($this->arResult['ITEMS'] as &$arItem){
                    $adr = [$arItem['PROPERTIES']['CITY']['VALUE'],$arItem['PROPERTIES']['STREET']['VALUE'],$arItem['PROPERTIES']['HOME']['VALUE'],$arItem['PROPERTIES']['APARTMENT']['VALUE']];
                    foreach ($adr as $key=> $value){
                        if(!$value)unset($adr[$key]);
                    }
                    $arItem['ADDRESS'] = implode(', ',$adr);;
                    $arItem['TIME'] = $arItem['PROPERTIES']['EXACT_TIME']['VALUE']?:$arItem['PROPERTIES']['DELIVERY_INTERVAL']['VALUE'];
                    $arItem['PROPERTIES']['RECIPIENT_NAME']['VALUE'] = $arItem['PROPERTIES']['RECIPIENT_NAME']['VALUE']?:$arItem['PROPERTIES']['NAME']['VALUE'];
                    $arItem['DELIVERY_DATE'] =  $arItem['PROPERTIES']['DELIVERY_DATE']['VALUE']?FormatDate('d F Y',strtotime($arItem['PROPERTIES']['DELIVERY_DATE']['VALUE'])):'';
                    $arItem['PROPERTIES']['SEND_PHOTO']['VALUE'] = $arItem['PROPERTIES']['SEND_PHOTO']['VALUE']?'Да':'Нет';
                    $arItem['COUPON_TEXT'] = $arItem['COUPON']['COUPON']?:'Не применялся';
                }
                unset($arItem);
            }

        }else{
            LocalRedirect("/?open_auth=Y");
        }

    }

    public function executeComponent()
    {
        try {
            if (!$this->extractDataFromCache()) {
                $this->initResult();
                $this->setResultCacheKeys(array_keys($this->arResult));
                $this->putDataToCache();
            }
            $this->includeComponentTemplate();

        } catch (SystemException $e) {
            $this->abortDataCache();

            ShowError($e->getMessage());
        }
    }

}
