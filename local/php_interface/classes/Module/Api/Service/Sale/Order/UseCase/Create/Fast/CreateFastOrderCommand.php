<?php

namespace Natix\Module\Api\Service\Sale\Order\UseCase\Create\Fast;

use Webmozart\Assert\Assert;

/**
 * Команда создания быстрого заказа
 *
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class CreateFastOrderCommand
{
    /** @var string */
    private $location;
    
    /** @var int|null */
    private $userId;
    
    /** @var int */
    private $personTypeId;
    
    /** @var string */
    private $propertyName;
    
    /** @var string */
    private $propertyEmail;
    
    /** @var string */
    private $propertyPersonalPhone;
    
    /** @var int */
    private $productId;
    
    public function __construct(
        string $location,
        ?int $userId,
        int $personTypeId,
        string $propertyName,
        string $propertyEmail,
        string $propertyPersonalPhone,
        int $productId
    ) {
        Assert::string($location, 'Код местоположения должен быть строкой');
        Assert::uuid($location, 'Код местоположения должен быть в формате uuid');
        Assert::nullOrInteger($userId, 'ID пользователя должен быть числом');
        Assert::greaterThan($userId, 0, 'ID пользователя должен быть больше 0');
        Assert::integer($personTypeId, 'ID пользователя должен быть числом');
        Assert::greaterThan($personTypeId, 0, 'ID пользователя должен быть больше 0');
        Assert::stringNotEmpty($propertyName, 'Имя пользователя должно быть заполнено');
        Assert::stringNotEmpty($propertyEmail, 'E-mail пользователя должен быть заполнен');
        Assert::email($propertyEmail, 'E-mail передан некорректно');
        Assert::stringNotEmpty($propertyPersonalPhone, 'Номер телефона пользователя должен быть заполнен');
        Assert::integer($productId, 'ID товара должен быть числом');
        Assert::greaterThan($productId, 0, 'ID товара должен быть числом');
        
        $this->location = $location;
        $this->userId = $userId;
        $this->personTypeId = $personTypeId;
        $this->propertyName = $propertyName;
        $this->propertyEmail = $propertyEmail;
        $this->propertyPersonalPhone = $propertyPersonalPhone;
        $this->productId = $productId;
    }

    /**
     * @param array $requestParams
     *
     * @return static
     */
    public static function fromArray(array $requestParams): self
    {
        return new self(
            $requestParams['LOCATION'],
            $requestParams['USER_ID'] ? (int)$requestParams['USER_ID'] : null,
            (int)$requestParams['PERSON_TYPE_ID'],
            $requestParams['PROPERTY_NAME'],
            $requestParams['PROPERTY_EMAIL'],
            $requestParams['PROPERTY_PERSONAL_PHONE'],
            (int)$requestParams['PRODUCT_ID']
        );
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getPersonTypeId(): int
    {
        return $this->personTypeId;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getPropertyEmail(): string
    {
        return $this->propertyEmail;
    }

    /**
     * @return string
     */
    public function getPropertyPersonalPhone(): string
    {
        return $this->propertyPersonalPhone;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }
}
