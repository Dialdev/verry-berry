<?php

namespace Natix\Module\Api\Service\Catalog\SetProduct\Command;

use Webmozart\Assert\Assert;

/**
 * Команда для запроса комбинации комплекта в карточке товара
 *
 * http://project.natix.ru/projects/53/tasks/4524
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetProductCommand
{
    /**
     * Идентификатор раздела
     *
     * @var int
     */
    private $sectionId;

    /**
     * Идентификатор букета
     *
     * @var int
     */
    private $setId;

    public function __construct(int $sectionId, int $setId)
    {
        Assert::greaterThan($sectionId, 0, 'Идентификатор раздела должен быть больше 0');
        Assert::greaterThan($setId, 0, 'Идентификатор букета должен быть больше 0');
        
        $this->sectionId = $sectionId;
        $this->setId = $setId;
    }

    /**
     * @param array $requestParams
     *
     * @return static
     */
    public static function fromArray(array $requestParams): self
    {
        return new self(
            (int)$requestParams['sectionId'],
            (int)$requestParams['setId']
        );
    }

    /**
     * @return int
     */
    public function getSectionId(): int
    {
        return $this->sectionId;
    }

    /**
     * @return int
     */
    public function getSetId(): int
    {
        return $this->setId;
    }
}
