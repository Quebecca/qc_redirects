<?php

return [
    'add_redirects' => [
        'path'   => '/addRedirects/new',
        'target' => \QcRedirects\Controller\AddRedirectsController::class.'::importAction'
    ]
];
