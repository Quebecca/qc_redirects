<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function () {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'QcRedirects',
            'QcRedirects',
            'Qc Redirects'
        );

        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'QcRedirects',
                'web', // Make module a submodule of 'web'
                'admin', // Submodule key
                '', // Position
                [
                    QcRedirects\Controller\AddRedirectsController::class => 'import',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:qc_redirects/Resources/Public/Icons/qc_redirects.svg',
                    'labels' => 'LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf'
                ]
            );

        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('qc_redirects', 'Configuration/TypoScript', 'Module be gestion des redirections');

    }
);
