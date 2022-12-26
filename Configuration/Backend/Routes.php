<?php

return [
    'add_redirects' => [
        'path'   => '/addRedirects/new',
        'target' => \Qc\QcRedirects\Controller\AddRedirectsController::class.'::importAction'
    ],
    //Backend Route link To Export Redirections list
    'export_redirects_list' => [
        'path' => '/export-redirects',
        'referrer' => 'required,refresh-empty',
        'target' =>  \Qc\QcRedirects\Controller\ExportRedirectActionController::class . '::exportRedirectsListAction'
    ],

];
