<?php

namespace Natix\Module\Api\Service\Catalog\SetProduct\Response;

use Webmozart\Assert\Assert;

/**
 * Элемент комбинаций размеров
 *
 * http://project.natix.ru/projects/53/tasks/4524
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SizeCombination
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;
    
    /** @var int|null */
    private $setId;

    /** @var bool */
    private $isActive;

    /** @var string|null */
    private $url;
    
    /** @var float|null */
    private $priceDiff;
    
    /** @var string|null */
    private $priceDiffFormat;
    
    public function __construct(
        int $id,
        string $name,
        bool $isActive,
        ?int $setId,
        ?string $url,
        ?float $priceDiff,
        ?string $priceDiffFormat
    ) {
        Assert::greaterThan($id, 0, 'Идентификатор размера должен быть больше 0');
        
        $this->id = $id;
        $this->name = $name;
        $this->isActive = $isActive;
        $this->setId = $setId;
        $this->url = $url;
        $this->priceDiff = $priceDiff;
        $this->priceDiffFormat = $priceDiffFormat;
    }

    /**
     * @param SizeCombination $sizeCombination
     *
     * @return array
     */
    public static function toState(SizeCombination $sizeCombination): array
    {
        return [
            'id' => $sizeCombination->id,
            'name' => $sizeCombination->name,
            'isActive' => $sizeCombination->isActive,
            'setId' => $sizeCombination->setId,
            'url' => $sizeCombination->url,
            'priceDiff' => $sizeCombination->priceDiff,
            'priceDiffFormat' => $sizeCombination->priceDiffFormat,            
        ];
    }
}
