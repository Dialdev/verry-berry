<?php

namespace Natix\Module\Api\Service\Sale\Order\Coupon;

use Natix\Module\Api\Exception\Sale\Order\Coupon\CouponServiceException;
use Natix\Service\Sale\Coupon\CouponApplyService;
use Natix\Service\Sale\Coupon\UserCouponEntity;
use Natix\Service\Sale\Coupon\UserCouponService;
use Quetzal\Service\Sale\Coupon\Exception\Service\DeleteCouponException;

/**
 * Обрабатывает методы АПИ применения/удаления/получения промокода
 * @link https://redmine.book24.ru/issues/30067
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BasketCouponService
{
    /** @var UserCouponService */
    private $userCouponService;
    
    /** @var CouponApplyService */
    private $couponApplyService;
    
    public function __construct(UserCouponService $userCouponService, CouponApplyService $couponApplyService)
    {
        $this->userCouponService = $userCouponService;
        $this->couponApplyService = $couponApplyService;
    }

    /**
     * Добавляет купон к пользователю к применённым
     *
     * @param $coupon
     *
     * @throws CouponServiceException
     * @throws \Natix\Service\Sale\Coupon\Exception\CouponApplyException
     * @throws \Natix\Service\Sale\Coupon\Exception\CouponApplyExpiredException
     */
    public function addCouponInUserStorage($coupon)
    {
        $coupon = $this->getPrepareCoupon($coupon);
        $this->couponApplyService->applyCoupon($coupon);
    }

    /**
     * @param $coupon
     * @return string
     * @throws CouponServiceException
     */
    private function getPrepareCoupon($coupon): string
    {
        $this->validateCoupon($coupon);
        $coupon = trim($coupon);
        return $coupon;
    }

    /**
     * Получение только действующих купонов
     * В случае если не найден или устарел выбрасывает исключение
     * 
     * @return array
     * @throws \Exception
     */
    public function getApplyCoupons(): array
    {
        $couponsList = $this->userCouponService->getApplyCoupons();
        $resultCoupons = [];
        foreach ($couponsList as $couponEntity) {
            $resultCoupons[] = $this->formatCoupon($couponEntity);
        }
        return $resultCoupons;
    }

    /**
     * Возвращает список применённых купонов у пользователя
     *
     * @return array
     * @throws \Exception
     */
    public function getCouponsFromUserStorage(): array
    {
        $couponsList = $this->userCouponService->getCouponsFromUserStorage();
        $resultCoupons = [];
        foreach ($couponsList as $couponEntity) {
            $resultCoupons[] = $this->formatCoupon($couponEntity);
        }
        return $resultCoupons;
    }

    /**
     * Удаляет купон из применённых у пользователя
     * @param $coupon
     *
     * @throws CouponServiceException
     * @throws DeleteCouponException
     */
    public function deleteCouponFromUserStorage($coupon)
    {
        $coupon = $this->getPrepareCoupon($coupon);
        $this->userCouponService->deleteCouponFromUserStorage($coupon);
    }

    /**
     * @param $coupon
     * @throws CouponServiceException
     */
    private function validateCoupon($coupon)
    {
        if (!is_scalar($coupon)) {
            throw new CouponServiceException('Купон должен иметь скалярный тип');
        }
        $coupon = trim($coupon);
        if (empty($coupon)) {
            throw new CouponServiceException('Купон не должен быть пустым');
        }
    }

    /**
     * @param UserCouponEntity $couponEntity
     * @return array
     */
    private function formatCoupon(UserCouponEntity $couponEntity): array
    {
        return [
            'COUPON' => $couponEntity->getCoupon(),
            'DESCRIPTION' => $couponEntity->getDescription(),
            'STATUS' => $couponEntity->getStatus(),
            'STATUS_TEXT' => $couponEntity->getStatusDescription(),
            'APPLY' => $couponEntity->isApply() ? 'Y' : 'N',
            'DISCOUNT_ID' => $couponEntity->getDiscountId(),
        ];
    }
}
