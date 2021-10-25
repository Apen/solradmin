<?php

declare(strict_types=1);

namespace Sng\Solradmin\Domain\Repository;

use function GuzzleHttp\json_decode;
use Sng\Solradmin\Domain\Model\Dto\SolrDemand;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AdminRepository
{
    /**
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand
     * @return object|null
     */
    public function findAll(SolrDemand $demand): ?object
    {
        return $this->getUrl($this->buildSelectUrl($demand));
    }

    /**
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand
     * @param string                                     $id
     */
    public function remove(SolrDemand $demand, string $id): void
    {
        $url = $this->buildBaseUrl($demand) . 'update';
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $additionalOptions = [
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Content-type' => 'text/xml'
            ],
            'body' => '<delete><query>id:' . $id . '</query></delete>'
        ];
        $response = $requestFactory->request($url, 'POST', $additionalOptions);
        $additionalOptions['body'] = '<commit />';
        $response = $requestFactory->request($url, 'POST', $additionalOptions);
    }

    /**
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand
     * @return string
     */
    public function buildBaseUrl(SolrDemand $demand): string
    {
        return $demand->getScheme() . '://' . $demand->getHost() . ':' . $demand->getPort() . $demand->getPath();
    }

    /**
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand
     * @return string
     */
    public function buildSelectUrl(SolrDemand $demand): string
    {
        $url = $this->buildBaseUrl($demand);
        $url .= 'select?q=' . $demand->getQuery();
        $url .= '&rows=' . $demand->getLimit();
        $url .= '&start=' . $demand->getStart();
        $url .= '&fl=' . $demand->getFieldList();
        return $url;
    }

    /**
     * @param string $url
     * @return object|null
     */
    protected function getUrl(string $url): ?object
    {
        $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
        $response = $requestFactory->request($url);
        if ($response->getStatusCode() === 200) {
            return \GuzzleHttp\json_decode($response->getBody()->getContents());
        }

        return null;
    }
}
