<?php


/**
 * Extension Manager/Repository config file for ext "qc_redirects".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Qc Redirects',
    'description' => "Extends Core's Redirects Module with a Title column, showing creation and modification date and more sorting and filtering options. Also add an import redirects functionality.",
    'author' => 'Quebec.ca',
    'category' => 'module',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'php' => '8.2',
            'typo3' => '13.4.0-13.4.99',
        ],
        'conflicts' => []
    ],
    'state' => 'stable'
];
