<?php

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

//$typoVersion = explode('.',GeneralUtility::makeInstance(Typo3Version::class)->getVersion())[0];
$typoVersion = GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion();
if($typoVersion == 10){
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Controller\ManagementController::class] = [
        'className' => QcRedirects\Controller\ExtendedRedirectModule\v10\ManagementControllerExt::class
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\RedirectRepository::class] = [
        'className' => QcRedirects\Controller\ExtendedRedirectModule\v10\RedirectRepositoryExt::class
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\Demand::class] = [
        'className' => QcRedirects\Controller\ExtendedRedirectModule\v10\DemandExt::class
    ];
}
else{

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Controller\ManagementController::class] = [
        'className' => QcRedirects\Controller\ExtendedRedirectModule\v11\ManagementControllerExtV11::class
    ];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\RedirectRepository::class] = [
        'className' => QcRedirects\Controller\ExtendedRedirectModule\v11\RedirectRepositoryExtV11::class
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\Demand::class] = [
        'className' => QcRedirects\Controller\ExtendedRedirectModule\v11\DemandExt::class
    ];
}


