<?php

namespace Natix\Module\Api\Http\Response\Entity;

/**
 * DTO описания постраничной навигации
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PaginationEntity implements ResponseEntityInterface
{
    /** @var int */
    private $total;

    /** @var int */
    private $count;

    /** @var int */
    private $perPage;

    /** @var int */
    private $currentPage;

    /** @var int */
    private $totalPages;

    /**
     * @param int $total всего элементов
     * @param int $count кол-во элементов в текущем ответе
     * @param int $perPage элементов на странице
     * @param int $currentPage текущая страница
     * @param int $totalPages всего страниц
     */
    public function __construct(int $total, int $count, int $perPage, int $currentPage, int $totalPages)
    {
        $this->count = $count;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->total = $total;
        $this->totalPages = $totalPages;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->getTotal(),
            'count' => $this->getCount(),
            'perPage' => $this->getPerPage(),
            'currentPage' => $this->getCurrentPage(),
            'totalPages' => $this->getTotalPages(),
        ];
    }
}
