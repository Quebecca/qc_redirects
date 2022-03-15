<?php

namespace QcRedirects\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Redirects\Controller\ManagementController;
use TYPO3\CMS\Redirects\Repository\Demand;
use TYPO3\CMS\Redirects\Repository\RedirectRepository;

class ManagementControllerExt extends ManagementController
{
    /**
     * @var string
     */
    protected string $orderBy = 'Asc';

    /**
     * @var string
     */
    protected string $orderType = '';


    protected const ORDER_BY_DEFAULT = 'createdon';

    protected const ORDER_BY_VALUES = [
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
     * @param string $templateName
     * @throws RouteNotFoundException
     */
    protected function initializeView(string $templateName)
    {
        parent::initializeView($templateName);
        $this->view->setTemplateRootPaths(['EXT:qc_redirects/Resources/Private/Templates/']);

        // orderBy
        $this->orderBy = (string)(GeneralUtility::_GP('orderBy') ?? self::ORDER_BY_DEFAULT);


        // Table header
        $sortActions = [];
        foreach (array_keys(self::ORDER_BY_VALUES) as $key) {
            $sortActions[$key] = $this->constructBackendUri(['orderBy' => $key]);
        }
        $this->orderBy = (string)(GeneralUtility::_GP('orderBy') ?? self::ORDER_BY_DEFAULT);
        $this->view->assign('sortActions', $sortActions);
        $this->view->assign('tableHeader', $this->getVariablesForTableHeader($sortActions));

    }
    /**
     * Injects the request object for the current request, and renders the overview of all redirects
     *
     * @param ServerRequestInterface $request the current request
     * @return ResponseInterface the response with the content
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->initializeView('redirectOverview');
        $this->overviewAction($request);
        $this->moduleTemplate->setContent($this->view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Show all redirects, and add a button to create a new redirect
     * @param ServerRequestInterface $request
     */
    protected function overviewAction(ServerRequestInterface $request)
    {
        $this->getButtons();
        $demand = Demand::createFromRequest($request);
        $redirectRepository = GeneralUtility::makeInstance(RedirectRepository::class, $demand);
        if(str_contains($this->orderBy, '_reverse')){
            $this->orderBy = str_replace('_reverse', '',$this->orderBy);
            $this->orderType = 'Desc';
        }
        $redirectRepository->setOrderBy($this->orderBy);
        $redirectRepository->setOrderType($this->orderType);
        $count = $redirectRepository->countRedirectsByByDemand();

        $this->view->assignMultiple([
            'redirects' => $redirectRepository->findRedirectsByDemand(),
            'hosts' => $redirectRepository->findHostsOfRedirects(),
            'statusCodes' => $redirectRepository->findStatusCodesOfRedirects(),
            'demand' => $demand,
            'showHitCounter' => GeneralUtility::makeInstance(Features::class)->isFeatureEnabled('redirects.hitCount'),
            'pagination' => $this->preparePagination($demand, $count),
        ]);
    }


    /**
     * @param array<string,mixed> $additionalQueryParameters
     * @param string $route
     * @return string
     * @throws RouteNotFoundException
     */
    protected function constructBackendUri(array $additionalQueryParameters = [], string $route = 'site_redirects'): string
    {
        $parameters = [
            'orderBy' => $this->orderBy,
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
     * Sets variables for the Fluid Template of the table with the Excluded Links
     * @param array<string,string> $sortActions
     * @return mixed[] variables
     */
    protected function getVariablesForTableHeader(array $sortActions): array
    {
        $headers = [
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
            $tableHeadData[$key]['label'] =  $key;

            if (isset($sortActions[$key])) {
                // sorting available, add url
                if ($this->orderBy === $key) {
                    $tableHeadData[$key]['url'] = $sortActions[$key . '_reverse'] ?? '';
                } else {
                    $tableHeadData[$key]['url'] = $sortActions[$key] ?? '';
                }

                // add icon only if this is the selected sort order
                if ($this->orderBy === $key) {
                    $tableHeadData[$key]['icon'] = 'status-status-sorting-asc';
                } elseif ($this->orderBy === $key . '_reverse') {
                    $tableHeadData[$key]['icon'] = 'status-status-sorting-desc';
                }
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