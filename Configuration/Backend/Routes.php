<?php

return [
    'add_redirects' => [
        'path'   => '/addRedirects/new',
        'target' => \Qc\QcRedirects\Controller\AddRedirectsController::class.'::importAction'
    ]
];
