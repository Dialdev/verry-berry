<?php

namespace Natix\Service\Core\Bundle\Exception;

class BadInstanceOfException extends \Exception
{
    public function __construct($class, $instanceClass)
    {
        parent::__construct(
            sprintf(
                'Класс %s должен быть наследником %s',
                $class,
                $instanceClass
            )
        );
    }
}
