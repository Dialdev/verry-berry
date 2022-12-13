<?php

namespace Natix\Data\Entity\Query;

/**
 * Класс служит аналогом для \Bitrix\Main\Entity\Query, но с отвязкой от битрикса
 */
class QueryFilter
{
    /**
     * @var array
     */
    protected $select = [];

    /**
     * @var array
     */
    protected $filter = [];

    /**
     * @var array
     */
    protected $group = [];

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var array
     */
    protected $having = [];

    /**
     * @var null|int
     */
    protected $limit = null;

    /**
     * @var null|int
     */
    protected $offset = null;

    /**
     * @param array $select
     * @return $this
     */
    public function setSelect(array $select): self
    {
        $this->select = $select;
        return $this;
    }

    /**
     * Adds a field for SELECT clause
     *
     * @param mixed $definition Field
     * @param string $alias Field alias like SELECT field AS alias
     * @return $this
     */
    public function addSelect($definition, $alias = ''): self
    {
        if (strlen($alias)) {
            $this->select[$alias] = $definition;
        } else {
            $this->select[] = $definition;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSelect(): array
    {
        return $this->select;
    }

    /**
     * @param array $filter
     * @return $this
     */
    public function setFilter(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Adds a filter for WHERE clause
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addFilter($key, $value): self
    {
        if ($key === null && is_array($value)) {
            $this->filter[] = $value;
        } else {
            $this->filter[$key] = $value;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $group
     * @return $this
     */
    public function setGroup(array $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Adds a field to the list of fields for GROUP BY clause
     *
     * @param $group
     * @return $this
     */
    public function addGroup($group): self
    {
        $this->group[] = $group;
        return $this;
    }

    /**
     * @return array
     */
    public function getGroup(): array
    {
        return $this->group;
    }

    /**
     * @param array $order
     * @return QueryFilter
     */
    public function setOrder(array $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Adds a filed to the list of fields for ORDER BY clause
     *
     * @param string $definition
     * @param string $order
     * @return $this
     */
    public function addOrder($definition, $order = 'ASC'): self
    {
        $this->order[$definition] = $order;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * @param array $having
     * @return $this
     */
    public function setHaving(array $having): self
    {
        $this->having = $having;
        return $this;
    }

    /**
     * @return array
     */
    public function getHaving(): array
    {
        return $this->having;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }
}
