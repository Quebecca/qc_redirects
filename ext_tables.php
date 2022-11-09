<?php
defined('TYPO3') || die('Access denied.');

call_user_func(
    function () {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'QcRedirects',
            'web', // Make module a submodule of 'web'
            'admin', // Submodule key
            '', // Position
            [
                Qc\QcRedirects\Controller\AddRedirectsController::class => 'import, reset',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:qc_redirects/Resources/Public/Icons/qc_redirects.svg',
                'labels' => 'LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf'
            ]
        );
    }
);
