<?php

use Qc\QcRedirects\Controller\AddRedirectsController;

return [
    'web_QcRedirects' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user,group',
        'icon' => 'EXT:qc_redirects/Resources/Public/Icons/qc_redirects.svg',
        'path' => '/module/web/QcRedirects',
        'labels' => 'LLL:EXT:qc_redirects/Resources/Private/Language/locallang.xlf',
        'extensionName' => 'QcRedirects',

        'controllerActions' => [
            AddRedirectsController::class => [
                'import', 'reset'
            ]
        ],
    ],
];
