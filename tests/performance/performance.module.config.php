<?php

return [

    'sds' => [
        'doctrineExtensions' => [
            'useDummyReader' => true,
            //'useDummyReader' => false,
            'extensionConfigs' => [
                'Sds\DoctrineExtensions\Dojo' => false,
                'Sds\DoctrineExtensions\Freeze' => false,
                'Sds\DoctrineExtensions\SoftDelete' => false,
                'Sds\DoctrineExtensions\State' => false,
                'Sds\DoctrineExtensions\Zone' => false,
            ],
        ],
    ],

    'doctrine' => [
        'configuration' => [
            'odm_default' => [
                //'metadata_cache'     => 'memcache',
                'metadata_cache'     => 'filesystem',
                //'metadata_cache'     => 'array',
                'generate_proxies'   => false,
                'generate_hydrators' => false,
            ],
        ],
        'cache' => [
            'filesystem' => [
                'class' => 'Doctrine\Common\Cache\PhpFileSerializeCache',
                'directory' => __DIR__ . '/cache/doctrine'
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
            'ViewHelperManager' => 'Sds\DoctrineExtensionsModule\Service\ViewHelperManagerFactory'
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
