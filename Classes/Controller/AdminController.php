<?php

declare(strict_types=1);

namespace Sng\Solradmin\Controller;

use Sng\Solradmin\Domain\Model\Dto\SolrDemand;
use Sng\Solradmin\Domain\Repository\AdminRepository;
use Sng\Solradmin\Pagination\SimplePagination;
use Sng\Solradmin\Service\AdminService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class AdminController extends ActionController
{
    protected ?AdminRepository $adminRepository = null;

    protected ?AdminService $adminService = null;

    /**
     * Output a list view of records
     *
     * @param array|null $overwriteDemand
     * @param int        $currentPage
     */
    public function listAction(?array $overwriteDemand = null, int $currentPage = 1): void
    {
        $demand = $this->createDemandObjectFromSettingsAndArguments();
        $demand = $this->overwriteDemandObject($demand, $overwriteDemand);
        $solrReponse = $this->adminRepository->findAll($demand);
        $this->buildPagination($solrReponse->response->numFound, $currentPage);
        $this->view->assign('currentPage', $currentPage);
        $this->view->assign('demand', $demand);
        $this->view->assign('overwriteDemand', $overwriteDemand);
        $this->view->assign('response', $solrReponse->response);
        $this->view->assign('query', $solrReponse->responseHeader);
        $this->view->assign('solrurl', $this->adminRepository->buildBaseUrl($demand));
        $this->view->assign('connections', $this->adminService->buildConnectionsSelect($this->settings['connections']));
    }

    /**
     * Output a list view of records
     *
     * @param string     $id
     * @param array|null $overwriteDemand
     * @param int        $currentPage
     */
    public function detailAction(string $id, ?array $overwriteDemand = null, int $currentPage = 1): void
    {
        $demand = $this->createDemandObjectFromSettingsAndArguments();
        $demand = $this->overwriteDemandObject($demand, $overwriteDemand);
        $demand->setQuery('id:' . $id);
        $demand->setFieldList('*');
        $demand->setStart(0);
        $solrReponse = $this->adminRepository->findAll($demand);
        $this->view->assign('currentPage', $currentPage);
        $this->view->assign('demand', $demand);
        $this->view->assign('overwriteDemand', $overwriteDemand);
        $this->view->assign('doc', get_object_vars($solrReponse->response->docs[0]));
        $this->view->assign('query', $solrReponse->responseHeader);
    }

    /**
     * Output a list view of records
     *
     * @param string     $id
     * @param array|null $overwriteDemand
     * @param int        $currentPage
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function deleteAction(string $id, ?array $overwriteDemand = null, int $currentPage = 1): void
    {
        $demand = $this->createDemandObjectFromSettingsAndArguments();
        $demand = $this->overwriteDemandObject($demand, $overwriteDemand);
        if (!empty($id)) {
            $this->adminRepository->remove($demand, $id);
        }

        $this->redirect(
            'list',
            'Admin',
            'solradmin',
            [
                'overwriteDemand' => $overwriteDemand,
                'currentPage' => $currentPage
            ]
        );
    }

    /**
     * @param int $nbItems
     * @param int $currentPage
     */
    protected function buildPagination(int $nbItems, int $currentPage): void
    {
        if ($nbItems > 0) {
            $pagination = GeneralUtility::makeInstance(
                SimplePagination::class,
                $nbItems,
                $currentPage,
                (int)$this->settings['itemsPerPage']
            );
            $pagination->setMaximumNumberOfLinks(10);
            $pagination->generate();
            $this->view->assign('pagination', $pagination);
        }
    }

    /**
     * Create the demand object which define which records will get shown
     *
     * @return \Sng\Solradmin\Domain\Model\Dto\SolrDemand
     */
    protected function createDemandObjectFromSettingsAndArguments(): SolrDemand
    {
        $demand = GeneralUtility::makeInstance(SolrDemand::class);
        $demand = $this->buildConnectionDemand($demand);

        if ($this->settings['itemsPerPage'] !== '') {
            $demand->setLimit((int)$this->settings['itemsPerPage']);
        }

        if ($this->request->hasArgument('currentPage')) {
            $demand->setStart(((int)$this->request->getArgument('currentPage') - 1) * (int)$this->settings['itemsPerPage']);
        }

        return $demand;
    }

    /**
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand
     * @param array                                      $connection
     * @return \Sng\Solradmin\Domain\Model\Dto\SolrDemand
     */
    protected function buildConnectionDemand(SolrDemand $demand, array $connection = []): SolrDemand
    {
        if (empty($connection)) {
            // get the first connection by default
            $connection = reset($this->settings['connections']);
        }

        if ($connection['scheme'] !== '') {
            $demand->setScheme($connection['scheme']);
        }

        if ($connection['host'] !== '') {
            $demand->setHost($connection['host']);
        }

        if ($connection['port'] !== '') {
            $demand->setPort($connection['port']);
        }

        if ($connection['path'] !== '') {
            $demand->setPath($connection['path']);
        }

        if ($connection['fieldList'] !== '') {
            $demand->setFieldList($connection['fieldList']);
        }

        return $demand;
    }

    /**
     * Overwrites a given demand object by an propertyName =>  $propertyValue array
     *
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand          Demand
     * @param array|null                                 $overwriteDemand OwerwriteDemand
     *
     * @return \Sng\Solradmin\Domain\Model\Dto\SolrDemand
     */
    protected function overwriteDemandObject(SolrDemand $demand, ?array $overwriteDemand = null): SolrDemand
    {
        if (
            (empty($this->settings['disableOverrideDemand']) && $overwriteDemand !== null) ||
            (!empty($this->settings['disableOverrideDemand']) && (int)$this->settings['disableOverrideDemand'] !== 1 && $overwriteDemand !== null)
        ) {
            foreach ($overwriteDemand as $propertyName => $propertyValue) {
                if ($propertyValue !== '' || $this->settings['allowEmptyStringsForOverwriteDemand']) {
                    ObjectAccess::setProperty($demand, $propertyName, $propertyValue);
                }
            }
        }

        if (!empty($overwriteDemand['connection'])) {
            $demand = $this->buildConnectionDemand($demand, $this->settings['connections'][$overwriteDemand['connection']]);
        }

        return $demand;
    }

    /**
     * @param \Sng\Solradmin\Domain\Repository\AdminRepository $adminRepository
     */
    public function injectAdminRepository(AdminRepository $adminRepository): void
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * @param \Sng\Solradmin\Service\AdminService $adminService
     */
    public function injectAdminService(AdminService $adminService): void
    {
        $this->adminService = $adminService;
    }
}
