<?php

namespace Natix\Module\Api\Service\Catalog\SetProduct\Response;

use Natix\Service\Catalog\Bouquets\Entity\ImageEntity;
use Natix\Service\Catalog\Bouquets\Entity\PriceEntity;
use Webmozart\Assert\Assert;

/**
 * Элемент комбинаций доп.ягод
 *
 * http://project.natix.ru/projects/53/tasks/4524
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class BerryCombination
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;
    
    /** @var string */
    private $cardName;

    /** @var bool */
    private $isActive;

    /** @var int|null */
    private $setId;

    /** @var string|null */
    private $url;
    
    /** @var ImageEntity */
    private $image;
    
    /** @var PriceEntity */
    private $price;
    
    public function __construct(
        int $id,
        string $name,
        string $cardName,
        bool $isActive,
        ?int $setId,
        ?string $url,
        ImageEntity $image,
        PriceEntity $price
    ) {
        Assert::greaterThan($id, 0, 'Идентификатор доп.ягоды должен быть больше 0');
        
        $this->id = $id;
        $this->name = $name;
        $this->cardName = $cardName;
        $this->isActive = $isActive;
        $this->setId = $setId;
        $this->url = $url;
        $this->image = $image;
        $this->price = $price;
    }

    /**
     * @param BerryCombination $berryCombination
     *
     * @return array
     */
    public static function toState(BerryCombination $berryCombination): array
    {
        return [
            'id' => $berryCombination->id,
            'name' => $berryCombination->name,
            'cardName' => $berryCombination->cardName,
            'isActive' => $berryCombination->isActive,
            'setId' => $berryCombination->setId,
            'url' => $berryCombination->url,
            'image' => ImageEntity::toState($berryCombination->image),
            'price' => PriceEntity::toState($berryCombination->price),
        ];
    }
}
