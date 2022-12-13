<?php

namespace Natix\Data\Repository;

use Bitrix\Main\Entity\Query;
use Natix\Data\Entity\Query\QueryFilter;

/**
 * Интерфейс репозитория
 */
interface RepositoryInterface
{
    /**
     * Возвращает созданный объект Query.
     *
     * Пример:
     * return SectionTable::query();
     *
     * @return Query
     */
    public function getObjectQuery(): Query;

    /**
     * Возвращает созданный объект фильтра.
     *
     * Для удобства лучше запихнуть в него сразу и select всех полей
     *
     * Пример:
     * return (new QueryFilter())
     *     ->setSelect(['*']);
     *
     * @return QueryFilter
     */
    public function getQueryFilter(): QueryFilter;

    /**
     * Конвертирует массив в объект сущности
     *
     * @param array $state
     * @return object
     */
    public function entityFromState(array $state);

    /**
     * Конвертирует объект сущности в массив
     *
     * @param object $entity
     * @return array
     */
    public function entityToState($entity): array;

    /**
     * Возвращает массив объектов сущности.
     * При реализации нужно поправить phpDoc return и указать массив каких объектов возвращает метод
     *
     * На вход принимает объект, полученный из метода $this->getQueryFilter()
     *
     * @param QueryFilter $queryFilter
     * @return array
     */
    public function findBy(QueryFilter $queryFilter): array;

    /**
     * Возвращает объект сущности.
     * Внутри себя использует метод $this->findBy() но с установленным лимитом в 1.
     *
     * При реализации нужно поправить phpDoc return и указать какого типа объект возвращает метод
     *
     * На вход принимает объект, полученный из метода $this->getQueryFilter()
     *
     * @param QueryFilter $queryFilter
     * @return null|object
     */
    public function findOneBy(QueryFilter $queryFilter);
}
