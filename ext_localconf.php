<?php

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'] += [
    TYPO3\CMS\Redirects\Controller\ManagementController::class => [
        'className' => Qc\QcRedirects\Controller\ExtendedRedirectModule\ManagementControllerExt::class
    ],
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\RedirectRepository::class] = [
    'className' => Qc\QcRedirects\Controller\ExtendedRedirectModule\RedirectRepositoryExt::class
];