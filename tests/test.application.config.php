<?php
return array(
    'modules' => array(
        'DoctrineModule',
        'DoctrineMongoODMModule',
        'Sds\Zf2ExtensionsModule',
        'Sds\DoctrineExtensionsModule'
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'vendor/superdweebie/doctrine-extensions-module/tests/test.module.config.php',
        ),
        'module_paths' => array(
            './vendor',
        ),
    ),
);
