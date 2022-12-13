<?php

namespace Natix\Service\Twig\Extension\Functions;

/**
 * Функция для твига - содержит счётчик вызовов,
 * в зависимости от чётного/нечётного вызова возвращает разную строку
 */
class OddEvenMod
{
    protected $loopName;

    protected $oddMod;

    protected $evenMod;

    protected $count = 0;

    /**
     * OddEvenMod constructor.
     * @param string $loopName - уникальное имя итератора
     * @param string $oddMod - строка которая вернется на чётной итерации
     * @param string $evenMod - строка которая вернется на нечётной итерации
     */
    public function __construct(string $loopName, string $oddMod, string $evenMod)
    {
        $this->loopName = $loopName;
        $this->oddMod = $oddMod;
        $this->evenMod = $evenMod;
    }

    /**
     * Увеличивает счетчик итератора на 1
     * Возвращает модификатора в зависиомсти от четности/нечетности итреррации
     * @return string
     */
    public function next() : string
    {
        $this->count++;
        return $this->count % 2 == 0 ? $this->oddMod : $this->evenMod;
    }

    /**
     * Ползволяет сдвинуть счетчик итератора
     * @param int $count
     */
    public function setIteratorCount(int $count)
    {
        $this->count = $count;
    }
}
