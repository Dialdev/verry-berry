<?php

namespace Natix\Service\Sale\Coupon;

use Bitrix\Sale\DiscountCouponsManager;
use Natix\Service\Sale\Coupon\Exception\CouponApplyException;
use Natix\Service\Sale\Coupon\Exception\CouponApplyExpiredException;

/**
 * Сервис для применения промокода
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CouponApplyService
{
    /** @var UserCouponService */
    private $userCouponService;

    /**
     * @param UserCouponService $userCouponService
     */
    public function __construct(UserCouponService $userCouponService)
    {
        $this->userCouponService = $userCouponService;
    }

    /**
     * Применяет переданный промокод
     *
     * @param string $coupon
     *
     * @throws CouponApplyException
     * @throws CouponApplyExpiredException
     */
    public function applyCoupon(string $coupon)
    {
        $this->checkCoupon($coupon);
        $this->applyCouponWithOutRestrictionCheck($coupon);
    }

    /**
     * Применяет переданы промокод, минуя внутренние проверки
     *
     * @param string $coupon
     *
     * @throws CouponApplyException
     */
    public function applyCouponWithOutRestrictionCheck(string $coupon)
    {
        $resultAddCoupon = DiscountCouponsManager::add($coupon);
        if (!$resultAddCoupon) {
            $errors = DiscountCouponsManager::getErrors();
            throw new CouponApplyException('Ошибка применения купона: ' . implode(', ', $errors));
        }
    }

    /**
     * Проверяет - доступен ли купон для применения
     *
     * @param string $coupon
     *
     * @throws CouponApplyException
     * @throws CouponApplyExpiredException
     */
    private function checkCoupon(string $coupon): void
    {
        $couponData = DiscountCouponsManager::getData($coupon);
        if ($couponData) {
            $this->userCouponService->isStatusExpireByCheckCode($couponData['CHECK_CODE']);
            if ($this->userCouponService->isStatusExpireByCheckCode($couponData['CHECK_CODE'])) {
                throw new CouponApplyExpiredException('Срок действия промокод истек');
            }
            if ($this->userCouponService->isStatusNotFoundByCheckCode($couponData['CHECK_CODE'])) {
                throw new CouponApplyException('Купон не найден');
            }
        }
    }
}
