<?php

namespace Natix\Module\Api\Http\Response\Entity;

use Natix\Data\Collection\Collection;

/**
 * DTO с коллекцией ошибок
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class ErrorEntityCollection extends Collection implements ResponseEntityInterface
{
    /**
     * {@inheritDoc}
     */
    public function __construct(array $errorsList)
    {
        foreach ($errorsList as $error) {
            if (!$error instanceof ErrorEntity) {
                throw new \InvalidArgumentException(sprintf(
                    'Массив для инициализации коллекции должен состоять только из объектов %s',
                    ErrorEntity::class
                ));
            }
        }
        parent::__construct($errorsList);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        if (!$value instanceof ErrorEntity) {
            throw new \InvalidArgumentException(sprintf(
                'Добавлять в  коллекции можно только объекты %s',
                ErrorEntity::class
            ));
        }
        parent::set($key, $value);
    }

    public function toArray(): array
    {
        $return = [];
        /** @var ErrorEntity $error */
        foreach ($this->all() as $error) {
            $return[] = $error->toArray();
        }
        return $return;
    }
}
