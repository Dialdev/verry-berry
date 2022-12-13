<?php

namespace Natix\Service\Catalog\Bouquets\Dto;

/**
 * DTO для параметров запроса коллекции комплектов
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class SetQueryParamsDto
{
    /**
     * @var array|null
     */
    private $filter;

    /**
     * @var string|null
     */
    private $sortField;

    /**
     * @var string|null
     */
    private $sortOrder;

    /**
     * @var int|null
     */
    private $limit;

    /**
     * @var int|null
     */
    private $offset;

    /**
     * @param array|null $filter
     * @param string|null $sortField
     * @param string|null $sortOrder
     * @param int|null $limit
     * @param int|null $offset
     */
    public function __construct(
        ?array $filter,
        ?string $sortField,
        ?string $sortOrder,
        ?int $limit,
        ?int $offset
    ) {
        $this->filter = $filter;
        $this->sortField = $sortField;
        $this->sortOrder = $sortOrder;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * @return array|null
     */
    public function getFilter(): ?array
    {
        return $this->filter;
    }

    /**
     * @param array|null $filter
     * @return SetQueryParamsDto
     */
    public function setFilter(?array $filter): SetQueryParamsDto
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSortField(): ?string
    {
        return $this->sortField;
    }

    /**
     * @param string|null $sortField
     * @return SetQueryParamsDto
     */
    public function setSortField(?string $sortField): SetQueryParamsDto
    {
        $this->sortField = $sortField;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }

    /**
     * @param string|null $sortOrder
     * @return SetQueryParamsDto
     */
    public function setSortOrder(?string $sortOrder): SetQueryParamsDto
    {
        $this->sortOrder = $sortOrder;
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
     * @param int|null $limit
     * @return SetQueryParamsDto
     */
    public function setLimit(?int $limit): SetQueryParamsDto
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @param int|null $offset
     * @return SetQueryParamsDto
     */
    public function setOffset(?int $offset): SetQueryParamsDto
    {
        $this->offset = $offset;
        return $this;
    }
}
