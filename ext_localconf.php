<?php

defined('TYPO3') || die();

$typoVersion = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion();



$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'] += [
    TYPO3\CMS\Redirects\Controller\ManagementController::class => [
        'className' => Qc\QcRedirects\Controller\ExtendedRedirectModule\ManagementControllerExt::class
    ],
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\RedirectRepository::class] = [
    'className' => Qc\QcRedirects\Controller\ExtendedRedirectModule\RedirectRepositoryExt::class
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    "@import 'EXT:qc_redirects/Configuration/TsConfig/pageconfig.tsconfig'"
);

