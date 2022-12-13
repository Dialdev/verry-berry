<?php

namespace Natix\Service\Sale\Coupon;

use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Order;
use Natix\Data\Bitrix\UserContainerInterface;
use Natix\Module\Api\Service\Sale\Order\OrderService;
use Natix\Service\Sale\Coupon\Exception\DeleteCouponException;

/**
 * Сервис взаимодействия с купонами в сессии пользователя
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class UserCouponService
{
    /** @var UserContainerInterface */
    private $userContainer;
    
    public function __construct(UserContainerInterface $userContainer)
    {
        $this->userContainer = $userContainer;
    }

    /**
     * Проверяет даты действия купона по коду его статуса
     * @param $checkCode
     * @return bool
     */
    public function isStatusExpireByCheckCode($checkCode): bool
    {
        return ($checkCode & DiscountCouponsManager::COUPON_CHECK_RANGE_ACTIVE_FROM_DISCOUNT)
            || ($checkCode & DiscountCouponsManager::COUPON_CHECK_RANGE_ACTIVE_TO_DISCOUNT)
            || ($checkCode & DiscountCouponsManager::COUPON_CHECK_RANGE_ACTIVE_FROM)
            || ($checkCode & DiscountCouponsManager::COUPON_CHECK_RANGE_ACTIVE_TO)
            || ($checkCode & DiscountCouponsManager::COUPON_CHECK_NO_ACTIVE)
            || ($checkCode & DiscountCouponsManager::COUPON_CHECK_NO_ACTIVE_DISCOUNT);
    }

    /**
     * Получение только применённых купонов из сессии пользователя
     * @return UserCouponEntity[]
     * @throws \Exception
     */
    public function getApplyCoupons(): array
    {
        $result = $this->getCouponsFromUserStorage();
        foreach ($result as $key => $coupon) {
            switch ($coupon->getStatus()) {
                case DiscountCouponsManager::STATUS_NOT_FOUND:
                case DiscountCouponsManager::STATUS_FREEZE:
                    unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * Получает купоны из сессии пользователя
     * @return UserCouponEntity[]
     * @throws \Exception
     */
    public function getCouponsFromUserStorage(): array
    {
        $coupons = DiscountCouponsManager::get(true, [], true);
        /* Купон может быть валидным, но не применённым, т.к. пользователь не выполнил условия акции
           Например, купон даёт подарок при покупке определённого товара, но этот товар пользователь
           не добавил в корзину */
        try {
            $basket = Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
            if ($basket === null) {
                throw new \Exception('Не удалось создать объект корзины');
            }
            $order = Order::create($basket->getSiteId(), $this->userContainer->getId());
            $order->setPersonTypeId(OrderService::PERSON_TYPE_ID_FIZ);
            $order->setBasket($basket);
        } catch (\Exception $exception) {
            throw new \Exception(
                sprintf('Не удалось создать объект заказа при получении купонов - %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }

        $appliedDiscounts = [];

        DiscountCompatibility::stopUsageCompatible();
        $discountApplyResult = $order->getDiscount()->getApplyResult();
        DiscountCompatibility::revertUsageCompatible();

        foreach ($discountApplyResult['DISCOUNT_LIST'] as $discount) {
            if ($discount['APPLY'] === 'Y') {
                $appliedDiscounts[$discount['REAL_DISCOUNT_ID']] = $discount;
            }
        }

        $resultCoupons = [];
        if (is_array($coupons) && count($coupons) > 0) {
            foreach ($coupons as $coupon) {
                if (
                    $this->isStatusNotFoundByCheckCode($coupon['CHECK_CODE'])
                    || $this->isStatusExpireByCheckCode($coupon['CHECK_CODE'])
                ) {
                    DiscountCouponsManager::delete($coupon['COUPON']);
                    continue;
                }

                $couponApplied = isset($appliedDiscounts[$coupon['DISCOUNT_ID']]) ? 'Y' : 'N';

                // Если это правило работы с корзиной и оно не применено, нужно сделать доп. проверку
                // т.к. если в правиле прописано действие - подарок за покупку, то пока этот подарок не положить в корзину
                // правило считается не применённым и отсутствует в массиве $order->getDiscount()->getApplyResult()['DISCOUNT_LIST']
                // А нам уже нужно показать пользователю сообщение о том, что условия промо-кода соблюдены
                // Судя по всему массив $discountApplyResult['FULL_DISCOUNT_LIST'] содержит список возможных скидок
                if (
                    $couponApplied !== 'Y'
                    && $coupon['MODULE'] === 'sale'
                    && array_key_exists($coupon['DISCOUNT_ID'], $discountApplyResult['FULL_DISCOUNT_LIST'])
                ) {
                    $couponApplied = 'Y';
                }

                $resultCoupons[] = new UserCouponEntity(
                    $coupon['COUPON'],
                    '',
                    (int)$coupon['STATUS'],
                    $coupon['STATUS_TEXT'] ?? '',
                    $couponApplied === 'Y',
                    (int)$coupon['DISCOUNT_ID']
                );
            }
        }
        return $resultCoupons;
    }

    /**
     * Проверяет существование купона по коду его статуса
     * @param $checkCode
     * @return bool
     */
    public function isStatusNotFoundByCheckCode($checkCode): bool
    {
        return (bool)($checkCode & DiscountCouponsManager::STATUS_NOT_FOUND);
    }

    /**
     * Удаляет купон из применённых у пользователя
     * @param $coupon
     * @throws DeleteCouponException
     */
    public function deleteCouponFromUserStorage(string $coupon): void
    {
        $coupon = strtoupper(trim($coupon));
        $resultDeleteCoupon = DiscountCouponsManager::delete($coupon);
        if (!$resultDeleteCoupon) {
            $errors = DiscountCouponsManager::getErrors();
            throw new DeleteCouponException(
                'Ошибка удаления купона' . (!empty($errors) ? ': ' . implode(', ', $errors) : '')
            );
        }
    }
}
