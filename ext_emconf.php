<?php

/**
 * Extension Manager/Repository config file for ext "qc_redirects".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'qc_redirects',
    'description' => 'Module used to improve the redirects module, by importing a redirects list with a title column',
    'category' => 'module',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'fluid_styled_content' => '10.4.0-11.5.99',
            'rte_ckeditor' => '10.4.0-11.5.99',
            'backend_module' => '2.2.1',
            'redirects' => '10.4.0-11.5.99'
        ],
        'conflicts' => [
        ],
    ],
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
];
