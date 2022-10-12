<?php
/***
 *
 * This file is part of Qc Redirects project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 <techno@quebec.ca>
 *
 ***/

namespace QcRedirects\Controller\ExtendedRedirectModule\v10;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use QcRedirects\Controller\BackendSession\BackendSession;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Redirects\Controller\ManagementController;

class ManagementControllerExt extends ManagementController
{
    /**
     * @var LocalizationUtility
     */
    protected $localizationUtility;

    /**
     * @var BackendSession
     */
    protected BackendSession $backendSession;

    /**
     * @var string
     */
    const QC_LANG_FILE = 'LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf:';
    const CORE_LANG_FILE = 'LLL:EXT:redirects/Resources/Private/Language/locallang_module_redirect.xlf:';

    protected const ORDER_BY_DEFAULT = 'createdon';
    protected const ORDER_TYPE_DEFAULT = 'DESC';

    protected const ORDER_BY_VALUES = [
        'title' => [
            ['title', 'ASC'],
        ],
        'title_reverse' => [
            ['title', 'DESC'],
        ],
        'source_host' => [
            ['source_host', 'ASC'],
        ],
        'source_host_reverse' => [
            ['source_host', 'DESC'],
        ],
        'source_path' => [
            ['source_path', 'ASC'],
        ],
        'source_path_reverse' => [
            ['source_path', 'DESC'],
        ],
        'createdon' => [
            ['createdon', 'ASC'],
        ],
        'createdon_reverse' => [
            ['createdon', 'DESC'],
        ],
    ];

    /**
     * @var DemandExt
     */
    protected DemandExt $demand;

    /**
     * Instantiate the form protection before a simulated user is initialized.
     */
    public function __construct()
    {
        parent::__construct();
        $this->localizationUtility = $localizationUtility ?? GeneralUtility::makeInstance(LocalizationUtility::class);
        $this->moduleTemplate->getPageRenderer()->addCssFile('EXT:qc_redirects/Resources/Public/Css/qc_redirects.css');
        $this->backendSession = $backendSession ?? GeneralUtility::makeInstance(BackendSession::class);
        $this->demand = $demand ?? GeneralUtility::makeInstance(DemandExt::class);
        $this->demand->setOrderBy(self::ORDER_BY_DEFAULT);
        $this->demand->setOrderType(self::ORDER_TYPE_DEFAULT);
        if($this->backendSession->get('qc_redirect_filterKey') != null){
            $this->demand = $this->backendSession->get('qc_redirect_filterKey');
        }
        else{
            // initialize the filter
            $this->updateFilter();
        }

    }

    /**
     * This function is used to manage filter and pagination
     */
    public function updateFilter(){
        $this->backendSession->store('qc_redirect_filterKey', $this->demand);
    }

    /**
     * @param string $templateName
     * @throws RouteNotFoundException
     */
    protected function initializeView(string $templateName) : void
    {
        parent::initializeView($templateName);
        $this->view->setTemplateRootPaths(['EXT:qc_redirects/Resources/Private/Templates/']);

        // orderBy
        $orderBy = (string)(GeneralUtility::_GP('orderBy'));
        if(in_array($orderBy, array_keys(self::ORDER_BY_VALUES))){
            $this->demand->setOrderBy((string)(GeneralUtility::_GP('orderBy')));
        }
        $this->demand->setOrderType(str_contains($this->demand->getOrderBy(), '_reverse') ? 'ASC' : 'DESC');
        // Table header
        $sortActions = [];
        foreach (array_keys(self::ORDER_BY_VALUES) as $key) {
            $sortActions[$key] = $this->constructBackendUri(['orderBy' => $key]);
        }
        $this->view->assign('sortActions', $sortActions);
        $this->view->assign('tableHeader', $this->getVariablesForTableHeader($sortActions));

    }

    /**
     * Injects the request object for the current request, and renders the overview of all redirects
     * This core function is overloaded to change the template
     *
     * @param ServerRequestInterface $request the current request
     * @return ResponseInterface the response with the content
     * @throws RouteNotFoundException
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->initializeView('redirectOverviewV10');
        $this->overviewAction($request);
        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Show all redirects, and add a button to create a new redirect
     * This overloaded function is used to add order column and the order type
     * @param ServerRequestInterface $request
     */
    protected function overviewAction(ServerRequestInterface $request): void
    {
        $this->getButtons();

        if($request->getQueryParams()['resetFilter'] == 'true'){
            $this->demand = new DemandExt();
        }
        else{
            $demand = DemandExt::createFromRequest($request);
            if($request->getParsedBody() != null){
                $this->demand->setTitle($demand->getTitle());
                $this->demand->setOrderType($demand->getOrderType());
                $this->demand->setOrderBy($demand->getOrderBy());
                $this->demand->setTarget($demand->getTarget());
                $this->demand->setSourceHost($demand->getSourceHost());
                $this->demand->setSourcePath($demand->getSourcePath());
                $this->demand->setLimit($demand->getLimit());
                $this->demand->setStatusCode($demand->getStatusCode());
            }
            $this->demand->setPage($demand->getPage());
        }
        $this->updateFilter();
        $redirectRepository = GeneralUtility::makeInstance(RedirectRepositoryExt::class, $this->demand);

        $redirectRepository->setOrderBy(str_replace('_reverse', '', $this->demand->getOrderBy()));
        $redirectRepository->setOrderType($this->demand->getOrderType());
        $count = $redirectRepository->countRedirectsByByDemand();
        $this->view->assignMultiple([
            'redirects' => $redirectRepository->findRedirectsByDemand(),
            'hosts' => $redirectRepository->findHostsOfRedirects(),
            'statusCodes' => $redirectRepository->findStatusCodesOfRedirects(),
            'demand' => $this->demand,
            'orderBy' => $this->demand->getOrderBy(),
            'orderType' => $this->demand->getOrderType(),
            'showHitCounter' => GeneralUtility::makeInstance(Features::class)->isFeatureEnabled('redirects.hitCount'),
            'pagination' => $this->preparePagination($this->demand, $count),
        ]);
    }

    /**
     * This function is used to build URI for sorting actions
     * @param array<string,mixed> $additionalQueryParameters
     * @param string $route
     * @return string
     * @throws RouteNotFoundException
     */
    protected function constructBackendUri(array $additionalQueryParameters = [], string $route = 'site_redirects'): string
    {
        $parameters = [
            'orderBy' => $this->demand->getOrderBy(),
        ];
        // if same key, additionalQueryParameters should overwrite parameters
        $parameters = array_merge($parameters, $additionalQueryParameters);

        /**
         * @var UriBuilder $uriBuilder
         */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return (string)$uriBuilder->buildUriFromRoute($route, $parameters);
    }


    /**
     * This function is used to generate headers of the redirects table in FE
     * @param array<string,string> $sortActions
     * @return mixed[] variables
     */
    protected function getVariablesForTableHeader(array $sortActions): array
    {
        $headers = [
            'title',
            'source_host',
            'source_path',
            'createdon'
        ];

        $tableHeadData = [];
        foreach ($headers as $key) {
            $tableHeadData[$key] = [
                'label' => '',
                'url'   => '',
                'icon'  => '',
            ];
            $lg_ext = (($key == 'createdon') || ($key == 'title')) ? self::QC_LANG_FILE : self::CORE_LANG_FILE ;
            $tableHeadData[$key]['label'] = $this->localizationUtility->translate($lg_ext .$key);
            if (isset($sortActions[$key])) {
                // sorting available, add url
                $tableHeadData[$key]['url'] = $this->demand->getOrderBy() === $key ? $sortActions[$key . '_reverse'] ?? '' : $sortActions[$key] ?? '';

                // add icon only if this is the selected sort order
                $tableHeadData[$key]['icon'] = $this->demand->getOrderBy() === $key
                    ? 'status-status-sorting-asc'
                    : ($this->demand->getOrderBy() === $key . '_reverse' ? 'status-status-sorting-desc' : '');
            }
        }
        $tableHeaderHtml = [];
        foreach ($tableHeadData as $key => $values) {
            if ($values['url'] !== '') {
                $tableHeaderHtml[$key]['header'] = sprintf(
                    '<a href="%s" style="text-decoration: underline;">%s</a>',
                    $values['url'],
                    $values['label']
                );
            } else {
                $tableHeaderHtml[$key]['header'] = $values['label'];
            }

            if ($values['icon'] !== '') {
                $tableHeaderHtml[$key]['icon'] = $values['icon'];
            }
        }
        return $tableHeaderHtml;
    }

}