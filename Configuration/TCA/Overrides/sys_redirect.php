<?php
defined('TYPO3') or die();

// Add some fields to sys_redirect table to show TCA fields definitions
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_redirect',
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
        'updatedon' => [
            'exclude' => true,
            'label' => 'updatedon',
            'config' => [
                'type' => 'input',
                'default' => '',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],

        'createdon' => [
            'exclude' => true,
            'label' => 'createdon',
            'config' => [
                'type' => 'input',
                'default' => '',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
    ]
);


// Feld einer neuen Palette hinzufügen
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'sys_redirect',
    'details',
    'title, createdon, updatedon'
);

// Neue Palette dem Tag hinzufügen, nach dem Titel - Dadurch Anzeige im Backend
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_redirect',
    '--palette--;;details',
    '',
    'before:source_host'
);
