<?php

/**
 * Extension Manager/Repository config file for ext "qc_redirects".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Qc Redirects',
    'description' => 'Module used to improve the redirects module, by importing a redirects list with a title column',
    'author' => 'Quebec.ca',
    'category' => 'module',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.6.99',
        ],
        'conflicts' => [
        ],
    ],
    'state' => 'beta',
    'version' => '1.0.0',
];
