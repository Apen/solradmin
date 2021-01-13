<?php

declare(strict_types=1);

namespace Sng\Solradmin\Domain\Model\Dto;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class SolrDemand extends AbstractEntity
{
    /**
     * @var string
     */
    protected $scheme = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var string
     */
    protected $port = '';

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var int
     */
    protected $limit = 10;

    /**
     * @var int
     */
    protected $start = 0;

    /**
     * @var string
     */
    protected $fieldList = '';

    /**
     * @var string
     */
    protected $query = '*:*';

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     */
    public function setScheme(string $scheme): void
    {
        $this->scheme = $scheme;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    /**
     * @return string
     */
    public function getFieldList(): string
    {
        return $this->fieldList;
    }

    /**
     * @return array
     */
    public function getFieldListArray(): array
    {
        return explode(',', $this->fieldList);
    }

    /**
     * @param string $fieldList
     */
    public function setFieldList(string $fieldList): void
    {
        $this->fieldList = $fieldList;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }
}
