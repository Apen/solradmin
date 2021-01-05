<?php

declare(strict_types=1);

namespace Sng\Solradmin\Pagination;

class SimplePagination
{
    /**
     * @var int
     */
    protected $nbItems = 0;

    /**
     * @var int
     */
    protected $currentPage = 1;

    /**
     * @var int
     */
    protected $numberOfPages = 0;

    /**
     * @var int
     */
    protected $maximumNumberOfLinks = 99;

    /**
     * @var int|float
     */
    protected $displayRangeStart;

    /**
     * @var int|float
     */
    protected $displayRangeEnd;

    /**
     * @var array
     */
    protected $pages = [];

    public function __construct(int $nbItems, int $currentPage, int $itemsPerPage)
    {
        $this->nbItems = $nbItems;
        $this->currentPage = $currentPage;
        $this->numberOfPages = max(1, (int)ceil($nbItems / $itemsPerPage));
    }

    public function getPreviousPageNumber(): ?int
    {
        $previousPage = $this->currentPage - 1;

        if ($previousPage > $this->numberOfPages) {
            return null;
        }

        return $previousPage >= $this->getFirstPageNumber()
            ? $previousPage
            : null;
    }

    public function getNextPageNumber(): ?int
    {
        $nextPage = $this->currentPage + 1;

        return $nextPage <= $this->numberOfPages
            ? $nextPage
            : null;
    }

    public function generate(): void
    {
        $this->calculateDisplayRange();
        $pages = [];
        for ($i = $this->displayRangeStart; $i <= $this->displayRangeEnd; $i++) {
            $pages[] = ['number' => $i, 'isCurrent' => $i === $this->currentPage];
        }
        $this->pages = $pages;
    }

    public function getFirstPageNumber(): int
    {
        return 1;
    }

    public function getLastPageNumber(): int
    {
        return $this->numberOfPages;
    }

    /**
     * If a certain number of links should be displayed, adjust before and after
     * amounts accordingly.
     */
    protected function calculateDisplayRange(): void
    {
        $maximumNumberOfLinks = $this->maximumNumberOfLinks;
        if ($maximumNumberOfLinks > $this->numberOfPages) {
            $maximumNumberOfLinks = $this->numberOfPages;
        }
        $delta = floor($maximumNumberOfLinks / 2);
        $this->displayRangeStart = $this->currentPage - $delta;
        $this->displayRangeEnd = $this->currentPage + $delta - ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);
        if ($this->displayRangeStart < 1) {
            $this->displayRangeEnd -= $this->displayRangeStart - 1;
        }
        if ($this->displayRangeEnd > $this->numberOfPages) {
            $this->displayRangeStart -= $this->displayRangeEnd - $this->numberOfPages;
        }
        $this->displayRangeStart = (int)max($this->displayRangeStart, 1);
        $this->displayRangeEnd = (int)min($this->displayRangeEnd, $this->numberOfPages);
    }

    /**
     * @param int $maximumNumberOfLinks
     */
    public function setMaximumNumberOfLinks(int $maximumNumberOfLinks): void
    {
        $this->maximumNumberOfLinks = $maximumNumberOfLinks;
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @return bool
     */
    public function getHasLessPages(): bool
    {
        return $this->displayRangeStart > 2;
    }

    /**
     * @return bool
     */
    public function getHasMorePages(): bool
    {
        return $this->displayRangeEnd + 1 < $this->numberOfPages;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

}
