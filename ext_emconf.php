<?php


/**
 * Extension Manager/Repository config file for ext "qc_redirects".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Qc Redirects',
    'description' => "Extends Core's Redirects Module with a Title column, showing creation and modification date and more sorting and filtering options. Also add an import redirects functionality.",
    'author' => 'Quebec.ca',
    'category' => 'module',
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'php' => '8.2',
            'typo3' => '12.4.0-12.9.99',
        ],
        'conflicts' => []
    ],
    'state' => 'stable'
];
