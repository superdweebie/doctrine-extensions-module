<?php
return array(
    'modules' => array(
        //'Sds\SlimClassmapModule',
        'Sds\ExceptionModule',
        'DoctrineModule',
        'DoctrineMongoODMModule',
        'Sds\DoctrineExtensionsModule'
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            'vendor/superdweebie/doctrine-extensions-module/tests/test.module.config.php',
            'vendor/superdweebie/doctrine-extensions-module/tests/performance/performance.module.config.php',
        ),
        'config_cache_enabled' => true,
        //'config_cache_enabled' => false,
        'cache_dir' => __DIR__ . '/cache',
        'check_dependencies' => false
    ),
);
