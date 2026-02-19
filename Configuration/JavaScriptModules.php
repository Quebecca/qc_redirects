<?php

return [
    'dependencies' => ['core', 'backend'],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@qc/qc-redirects/' => 'EXT:qc_redirects/Resources/Public/JavaScript/',
    ],
];
