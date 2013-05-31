<?php

return [

    'sds' => [
        'doctrineExtensions' => [
            'extensionConfigs' => [
                'extension.dojo' => false,
                'extension.freeze' => false,
                'extension.softdelete' => false,
                'extension.state' => false,
                'extension.zone' => false,
            ],
        ],
    ],

    'doctrine' => [
        'odm' => [
            'configuration' => [
                'default' => [
                    //'metadata_cache'     => 'doctrine.cache.memcache',
                    'metadata_cache'     => 'doctrine.cache.phpfileserialize',
                    //'metadata_cache'     => 'doctrine.cache.array',
                    'generate_proxies'   => false,
                    'generate_hydrators' => false,
                ],
            ],
        ],
        'cache' => [
            'phpfileserialize' => [
                'directory' => __DIR__ . '/cache/doctrine',
            ]
        ]
    ],

    'controllers' => [
        'invokables' => [
            'TestData' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\TestDataController'
        ],
    ],

    'service_manager' => array(
        'invokables' => [
            'DoctrineModule\Service\DriverFactory' => 'Sds\DoctrineExtensionsModule\Service\DriverFactory'
        ],
        'factories' => array(
            'my_memcache_alias' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\MemcacheFactory',
        )
    ),

    'router' => array(
        'routes' => array(
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'DoctrineExtensionsModule\TestData\create' => array(
                    'options' => array(
                        'route'    => 'create',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Sds\DoctrineExtensionsModule\Test\TestAsset',
                            'controller' => 'TestData',
                            'action'     => 'create'
                        )
                    )
                ),
                'DoctrineExtensionsModule\TestData\remove' => array(
                    'options' => array(
                        'route'    => 'remove',
                        'defaults' => array(
                            '__NAMESPACE__' => 'Sds\DoctrineExtensionsModule\Test\TestAsset',
                            'controller' => 'TestData',
                            'action'     => 'remove'
                        )
                    )
                ),
            )
        )
    ),
];
