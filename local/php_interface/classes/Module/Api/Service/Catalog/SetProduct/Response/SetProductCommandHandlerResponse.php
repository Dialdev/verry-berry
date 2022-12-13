<?php

namespace Natix\Module\Api\Service\Catalog\SetProduct\Response;

use Natix\Service\Catalog\Bouquets\Entity\SetEntity;

/**
 * Результат выполнения обработчика команды запроса комбинации комплекта в карточке товара
 *
 * http://project.natix.ru/projects/53/tasks/4524
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetProductCommandHandlerResponse
{
    /**
     * Сущность букета
     * 
     * @var SetEntity
     */
    private $setEntity;

    /**
     * Список доступных комбинаций для букета
     * 
     * @var Combinations
     */
    private $combinations;
    
    public function __construct(SetEntity $setEntity, Combinations $combinations)
    {
        $this->setEntity = $setEntity;
        $this->combinations = $combinations;
    }

    /**
     * @param SetProductCommandHandlerResponse $response
     *
     * @return array
     */
    public static function toState(self $response): array
    {
        return [
            'set' => SetEntity::toState($response->setEntity),
            'combinations' => Combinations::toState($response->combinations),
        ];
    }
}
