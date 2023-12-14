<?php

defined('TYPO3') || die();

$typoVersion = TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Core\Information\Typo3Version::class)->getMajorVersion();

if($typoVersion == 11){
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\RedirectRepository::class] = [
        'className' => Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\RedirectRepositoryExt::class
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\Demand::class] = [
        'className' => Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\DemandExt::class
    ];
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'] += [
    TYPO3\CMS\Redirects\Controller\ManagementController::class => [
        'className' => $typoVersion == 11 ? Qc\QcRedirects\Controller\ExtendedRedirectModule\v11\ManagementControllerExt::class
            : Qc\QcRedirects\Controller\ExtendedRedirectModule\v12\ManagementControllerExt::class
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    "@import 'EXT:qc_redirects/Configuration/TsConfig/pageconfig.tsconfig'"
);

