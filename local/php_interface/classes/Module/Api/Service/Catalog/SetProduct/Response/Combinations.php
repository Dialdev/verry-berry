<?php

namespace Natix\Module\Api\Service\Catalog\SetProduct\Response;

/**
 * Сущность доступных комбинаций для букета
 *
 * http://project.natix.ru/projects/53/tasks/4524
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Combinations
{
    /**
     * Список элементов комбинаций размеров
     *
     * @var SizeCombination[]
     */
    private $sizes;

    /**
     * Список элементов комбинаций доп. ягод 
     * 
     * @var BerryCombination[]
     */
    private $berries;

    /**
     * Список элементов комбинаций упаковок
     *
     * @var PackingCombination[]
     */
    private $packaging;
    
    public function __construct(array $sizes, array $berries, array $packaging) {
        $this->sizes = $sizes;
        $this->berries = $berries;
        $this->packaging = $packaging;
    }

    /**
     * @param Combinations $combinations
     *
     * @return array
     */
    public static function toState(Combinations $combinations): array
    {
        return [
            'sizes' => array_map(function (SizeCombination $sizeCombination) {
                return SizeCombination::toState($sizeCombination);
            }, $combinations->sizes),
            'berries' => array_map(function (BerryCombination $berryCombination) {
                return BerryCombination::toState($berryCombination);
            }, $combinations->berries),
            'packaging' => array_map(function (PackingCombination $packagingCombination) {
                return PackingCombination::toState($packagingCombination);
            }, $combinations->packaging),
        ];
    }
}
