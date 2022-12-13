<?php

namespace Natix\Data\Repository;

use Bitrix\Main\Entity\Query;
use Natix\Data\Entity\Query\QueryFilter;

/**
 * Базовый класс репозитория
 *
 * @task https://redmine.eksmo.ru/issues/6592
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * Создаёт битриксовый объет Query на основе QueryFilter и $this->getObjectQuery()
     *
     * @param QueryFilter $queryFilter
     * @return Query
     */
    protected function buildObjectQuery(QueryFilter $queryFilter): Query
    {
        $query = $this->getObjectQuery()
            ->setSelect($queryFilter->getSelect())
            ->setFilter($queryFilter->getFilter())
            ->setGroup($queryFilter->getGroup())
            ->setLimit($queryFilter->getLimit())
            ->setOffset($queryFilter->getOffset())
            ->setOrder($queryFilter->getOrder());

        return $query;
    }

    /**
     * Возвращает массив объектов сущности.
     *
     * При наследовании нужно добавить phpDoc с подписью - массив каких объектов возвращает метод
     *
     * На вход принимает объект, полученный из метода $this->getQueryFilter()
     *
     * @param QueryFilter $queryFilter
     * @return object[]
     */
    public function findBy(QueryFilter $queryFilter): array
    {
        $query = $this->buildObjectQuery($queryFilter);

        $result = $query->exec();

        $items = [];

        while ($item = $result->fetch()) {
            $items[] = $this->entityFromState($item);
        }

        return $items;
    }

    /**
     * Возвращает объект сущности или null.
     *
     * При наследовании нужно добавить phpDoc в подписью - какоц объект возвращает метод
     * Пример есть в классе \Natix\Service\Catalog\Lists\Categories\Repository\CategoriesRepository
     *
     * На вход принимает объект, полученный из метода $this->getQueryFilter()
     *
     * @param QueryFilter $queryFilter
     * @return null|object
     */
    public function findOneBy(QueryFilter $queryFilter)
    {
        $queryFilter->setLimit(1);

        $result = $this->findBy($queryFilter);

        return !empty($result) ? $result[0] : null;
    }
}
