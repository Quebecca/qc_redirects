<?php
defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Controller\ManagementController::class] = [
    'className' => QcRedirects\Controller\ManagementControllerExt::class
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Redirects\Repository\RedirectRepository::class] = [
    'className' => QcRedirects\Controller\RedirectRepositoryExt::class
];