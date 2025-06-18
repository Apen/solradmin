<?php

declare(strict_types=1);

namespace Sng\Solradmin\Controller;

use Psr\Http\Message\ResponseInterface;
use Sng\Solradmin\Domain\Model\Dto\SolrDemand;
use Sng\Solradmin\Domain\Repository\AdminRepository;
use Sng\Solradmin\Pagination\SimplePagination;
use Sng\Solradmin\Service\AdminService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class AdminController extends ActionController
{
    protected ?AdminRepository $adminRepository = null;

    protected ?AdminService $adminService = null;

    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct(ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function initializeAction(): void
    {
        if ($this->arguments->hasArgument('overwriteDemand')) {
            $propertyMappingConfiguration = $this->arguments->getArgument('overwriteDemand')->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->allowProperties('query');
            $propertyMappingConfiguration->allowProperties('connection');
        }
    }

    /**
     * Output a list view of records
     *
     * @param SolrDemand|null $overwriteDemand
     * @param int $currentPage
     */
    public function listAction(?SolrDemand $overwriteDemand = null, int $currentPage = 1): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $demand = $this->createDemandObjectFromSettingsAndArguments();
        $demand = $this->overwriteDemandObject($demand, $overwriteDemand);
        $solrReponse = $this->adminRepository->findAll($demand);
        $this->buildPagination($solrReponse->response->numFound, $currentPage, $moduleTemplate);
        $moduleTemplate->assign('currentPage', $currentPage);
        $moduleTemplate->assign('demand', $demand);
        $moduleTemplate->assign('overwriteDemand', $overwriteDemand);
        $moduleTemplate->assign('overwriteDemandArray', $overwriteDemand !== null ? $overwriteDemand->toArray() : []);
        $moduleTemplate->assign('response', $solrReponse->response);
        $moduleTemplate->assign('query', $solrReponse->responseHeader);
        $moduleTemplate->assign('solrurl', $this->adminRepository->buildBaseUrl($demand));
        $moduleTemplate->assign('connections', $this->adminService->buildConnectionsSelect($this->settings['connections']));
        $moduleTemplate->getDocHeaderComponent()->disable();
        return $moduleTemplate->renderResponse('Admin/List');
    }

    /**
     * Output a list view of records
     *
     * @param string $solrid
     * @param array $overwriteDemand
     * @param int $currentPage
     */
    public function detailAction(string $solrid, array $overwriteDemand = [], int $currentPage = 1): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $demand = $this->createDemandObjectFromSettingsAndArguments();
        $demand->setQuery('id:' . $solrid);
        $demand->setFieldList('*');
        $demand->setStart(0);
        $solrReponse = $this->adminRepository->findAll($demand);
        $moduleTemplate->assign('currentPage', $currentPage);
        $moduleTemplate->assign('demand', $demand);
        $moduleTemplate->assign('overwriteDemand', $overwriteDemand);
        $moduleTemplate->assign('doc', get_object_vars($solrReponse->response->docs[0]));
        $moduleTemplate->assign('query', $solrReponse->responseHeader);
        $moduleTemplate->getDocHeaderComponent()->disable();
        return $moduleTemplate->renderResponse('Admin/Detail');
    }

    /**
     * Output a list view of records
     *
     * @param string $solrid
     * @param array|null $overwriteDemand
     * @param int $currentPage
     */
    public function deleteAction(string $solrid, ?array $overwriteDemand = null, int $currentPage = 1): ResponseInterface
    {
        $demand = $this->createDemandObjectFromSettingsAndArguments();
        if (!empty($solrid)) {
            $this->adminRepository->remove($demand, $solrid);
        }

        return $this->redirect(
            'list',
            'Admin',
            'solradmin',
            [
                'overwriteDemand' => $overwriteDemand,
                'currentPage' => $currentPage,
            ]
        );
    }

    /**
     * @param int $nbItems
     * @param int $currentPage
     */
    protected function buildPagination(int $nbItems, int $currentPage, $moduleTemplate): void
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
            $moduleTemplate->assign('pagination', $pagination);
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

        if ($this->request->hasArgument('overwriteDemand')) {
            $overwriteDemand = $this->request->getArgument('overwriteDemand');
            if ($overwriteDemand['query'] !== '') {
                $demand->setQuery($overwriteDemand['query']);
            }
            if ($overwriteDemand['connection'] !== '') {
                $demand = $this->buildConnectionDemand($demand, $this->settings['connections'][$overwriteDemand['connection']]);
            }
        }

        $querySaved = $GLOBALS['BE_USER']->getModuleData('system_solradmin/list/query');
        if ($querySaved ?? false) {
            $demand->setQuery($querySaved);
        }

        return $demand;
    }

    /**
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand
     * @param array $connection
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
     * @param \Sng\Solradmin\Domain\Model\Dto\SolrDemand $demand Demand
     * @param SolrDemand|null $overwriteDemand OwerwriteDemand
     *
     * @return \Sng\Solradmin\Domain\Model\Dto\SolrDemand
     */
    protected function overwriteDemandObject(SolrDemand $demand, ?SolrDemand $overwriteDemand = null): SolrDemand
    {
        if ($overwriteDemand !== null) {
            if ($overwriteDemand->getQuery() !== '*:*') {
                $demand->setQuery($overwriteDemand->getQuery());
            }
            if ($overwriteDemand->getConnection() !== '') {
                $demand = $this->buildConnectionDemand(
                    $demand,
                    $this->settings['connections'][$overwriteDemand->getConnection()]
                );
            }
        }

        // store demand on user
        $GLOBALS['BE_USER']->pushModuleData('system_solradmin/list/query', $demand->getQuery());

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
