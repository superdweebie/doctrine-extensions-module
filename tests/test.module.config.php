<?php

return [
    'sds' => [
        'doctrineExtensions' => [
            'extensionConfigs' => [
                'Sds\DoctrineExtensions\AccessControl' => null,
                'Sds\DoctrineExtensions\Accessor' => null,
                'Sds\DoctrineExtensions\Annotation' => null,
                'Sds\DoctrineExtensions\Audit' => null,
                'Sds\DoctrineExtensions\Crypt' => null,
                'Sds\DoctrineExtensions\DoNotHardDelete' => null,
                'Sds\DoctrineExtensions\Dojo' => [
                    'destPaths' => [
                        'all' => [
                            'filter' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document',
                            'path' => 'data'
                        ],
                    ],
                ],
                'Sds\DoctrineExtensions\Freeze' => null,
                'Sds\DoctrineExtensions\Readonly' => null,
                'Sds\DoctrineExtensions\Rest' => ['basePath' => 'http://test.com/api'],
                'Sds\DoctrineExtensions\Serializer' => null,
                'Sds\DoctrineExtensions\SoftDelete' => null,
                'Sds\DoctrineExtensions\Stamp' => null,
                'Sds\DoctrineExtensions\State' => null,
                'Sds\DoctrineExtensions\Validator' => null,
                'Sds\DoctrineExtensions\Workflow' => null,
                'Sds\DoctrineExtensions\Zone' => null,
            ],
        ],
    ],

    'doctrine' => [
        'configuration' => [
            'odm_default' => [
                'default_db' => 'doctrineExtensionsModuleTest',
                'proxy_dir'    => __DIR__ . '/Proxy',
                'hydrator_dir' => __DIR__ . '/Hydrator',
            ],
        ],
        'driver' => [
            'odm_default' => [
                'drivers' => [
                    'Sds\DoctrineExtensionsModule\Test\TestAsset\Document' => 'test'
                ],
            ],
            'test' => [
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => [
                    __DIR__.'/Sds/DoctrineExtensionsModule/Test/TestAsset/Document'
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            'Sds\DoctrineExtensionsModule\Test\TestAsset\GameController' => function(){
                return new Sds\DoctrineExtensionsModule\Controller\JsonRestfulController(
                    ['documentClass' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game']
                );
            },
            'Sds\DoctrineExtensionsModule\Test\TestAsset\RoadController' => function(){
                return new Sds\DoctrineExtensionsModule\Controller\JsonRestfulController(
                    ['documentClass' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Road']
                );
            }
        ],
    ],

    'router' => [
        'routes' => [
            'Sds\Zf2ExtensionsModule\RestRoute' => [
                'type' => 'Sds\Zf2ExtensionsModule\RestRoute',                
                'options' => [
                    'route' => '/api', 
                    'defaults' => [
                        'controller' => 'Sds\DoctrineExtensionsModule\Controller\JsonRestfulController'
                    ],                       
                    'endpointToControllerMap' => [
                        'road' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\RoadController'
                    ],
                ],             
            ],
        ],
    ],
];
