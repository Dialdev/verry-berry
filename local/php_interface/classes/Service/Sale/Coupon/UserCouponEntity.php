<?php

namespace Natix\Service\Sale\Coupon;

/**
 * Объект купона, применённого пользователем
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class UserCouponEntity
{
    /** @var string */
    private $coupon;

    /** @var string */
    private $description;

    /** @var int */
    private $status;

    /** @var string */
    private $statusDescription;

    /** @var bool */
    private $isApply;

    /** @var int */
    private $discountId;

    /**
     * @param string $coupon
     * @param string $description
     * @param int $status
     * @param string $statusDescription
     * @param bool $isApply
     * @param int $discountId
     */
    public function __construct(
        string $coupon,
        string $description,
        int $status,
        string $statusDescription,
        bool $isApply,
        int $discountId
    ) {
        $this->coupon = $coupon;
        $this->description = $description;
        $this->status = $status;
        $this->statusDescription = $statusDescription;
        $this->isApply = $isApply;
        $this->discountId = $discountId;
    }

    /**
     * Купон
     * @return string
     */
    public function getCoupon(): string
    {
        return $this->coupon;
    }

    /**
     * Описание купона
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Статус купона
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Описание статуса купона
     * @return string
     */
    public function getStatusDescription(): string
    {
        return $this->statusDescription;
    }

    /**
     * Применен ли купон
     * @return bool
     */
    public function isApply(): bool
    {
        return $this->isApply;
    }

    /**
     * ID связанной с купоном скидки
     * @return int
     */
    public function getDiscountId(): int
    {
        return $this->discountId;
    }
}
