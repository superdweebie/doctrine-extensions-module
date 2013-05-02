<?php
return [
    'modules' => [
        'Sds\ExceptionModule',
        'DoctrineModule',
        'DoctrineMongoODMModule',
        'Sds\DoctrineExtensionsModule'
    ],
    'module_listener_options' => [
        'config_glob_paths'    => [
            __DIR__ . '/test.module.config.php',
        ],
    ],
];
