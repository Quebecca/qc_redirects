<?php
defined('TYPO3') or die();

// Add some fields to fe_users table to show TCA fields definitions
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_redirect',
    [
        'title' => [
            'exclude' => true,
            'label' => 'title',
            'config' => [
                'type' => 'input',
                'default' => '',
                'eval' => 'trim'
            ]
        ],

    ]
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_redirect',
    'title',
    '',
    'before:source_host'
);
