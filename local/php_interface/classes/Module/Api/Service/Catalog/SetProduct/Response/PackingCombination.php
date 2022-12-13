<?php

namespace Natix\Module\Api\Service\Catalog\SetProduct\Response;

use Natix\Service\Catalog\Bouquets\Entity\ImageEntity;
use Webmozart\Assert\Assert;

/**
 * Элемент комбинаций упаковок
 *
 * http://project.natix.ru/projects/53/tasks/4524
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PackingCombination
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
    
    public function __construct(
        int $id,
        string $name,
        string $cardName,
        bool $isActive,
        ?int $setId,
        ?string $url,
        ImageEntity $image
    ) {
        Assert::greaterThan($id, 0, 'Идентификатор упаковки должен быть больше 0');
        
        $this->id = $id;
        $this->name = $name;
        $this->cardName = $cardName;
        $this->isActive = $isActive;
        $this->setId = $setId;
        $this->url = $url;
        $this->image = $image;
    }

    /**
     * @param PackingCombination $packingCombination
     *
     * @return array
     */
    public static function toState(PackingCombination $packingCombination): array
    {
        return [
            'id' => $packingCombination->id,
            'name' => $packingCombination->name,
            'cardName' => $packingCombination->cardName,
            'isActive' => $packingCombination->isActive,
            'setId' => $packingCombination->setId,
            'url' => $packingCombination->url,
            'image' => ImageEntity::toState($packingCombination->image),
        ];
    }
}
