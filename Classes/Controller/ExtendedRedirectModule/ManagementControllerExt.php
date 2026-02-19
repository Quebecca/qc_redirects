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

namespace Qc\QcRedirects\Controller\ExtendedRedirectModule;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Controller\ManagementController;
use TYPO3\CMS\Redirects\Event\ModifyRedirectManagementControllerViewDataEvent;
use TYPO3\CMS\Redirects\Utility\RedirectConflict;


class ManagementControllerExt extends ManagementController{

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $view = $this->moduleTemplateFactory->create($request);
        $demand = DemandExt::fromRequest($request);

        $view->setTitle(
            $this->getLanguageService()->sL('LLL:EXT:redirects/Resources/Private/Language/locallang_module_redirect.xlf:mlang_tabs_tab')
        );

        $this->registerDocHeaderButtons($view, $request->getAttribute('normalizedParams')->getRequestUri());

        if (!$this->canListRedirects()) {
            return $view->renderResponse('RedirectOverview');
        }

        $event = $eventDispatcher->dispatch(
            new ModifyRedirectManagementControllerViewDataEvent(
                $demand,
                $this->redirectRepository->findRedirectsByDemand($demand),
                $this->redirectRepository->findHostsOfRedirects(),
                $this->redirectRepository->findStatusCodesOfRedirects(),
                $this->redirectRepository->findCreationTypes(),
                GeneralUtility::makeInstance(Features::class)->isFeatureEnabled('redirects.hitCount'),
                $view,
                $request,
                $this->redirectRepository->findIntegrityStatusCodes()
            )
        );

        $view = $event->getView();
        $hasEditPermissions = $this->canEditRedirects();
        $view->assignMultiple([
            'redirects' => $event->getRedirects(),
            'hosts' => $event->getHosts(),
            'statusCodes' => $event->getStatusCodes(),
            'creationTypes' => $event->getCreationTypes(),
            'integrityStatusCodes' => $event->getIntegrityStatusCodes(),
            'defaultIntegrityStatus' => RedirectConflict::NO_CONFLICT,
            'demand' => $event->getDemand(),
            'showHitCounter' => $event->getShowHitCounter(),
            'pagination' => $this->preparePagination($event->getDemand()),
            'canEditRedirects' => $hasEditPermissions,
            'canListRedirects' => true,
        ]);
        return $view->renderResponse('RedirectOverview');
    }

}
