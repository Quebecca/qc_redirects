<?php

defined('TYPO3') || die();

$typoVersion = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'] = [
    TYPO3\CMS\Redirects\Controller\ManagementController::class => [
        'className' => $typoVersion == 10 ? Qc\QcRedirects\Controller\ExtendedRedirectModule\v10\ManagementControllerExt::class
            : Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\ManagementControllerExt::class
    ],
    TYPO3\CMS\Redirects\Repository\RedirectRepository::class => [
        'className' => $typoVersion == 10 ? Qc\QcRedirects\Controller\ExtendedRedirectModule\v10\RedirectRepositoryExt::class
            : Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\RedirectRepositoryExt::class
    ],
    TYPO3\CMS\Redirects\Repository\Demand::class => [
        'className' => $typoVersion == 10 ? Qc\QcRedirects\Controller\ExtendedRedirectModule\v10\DemandExt::class
            : Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\DemandExt::class
    ]
];

