<?php
return [
    'sds' => [
        'doctrineExtensions' => [
            'doctrine' => [
                'driver' => 'odm_default',
                'eventmanager' => 'odm_default',
                'configuration' => 'odm_default',
                'documentManager' => 'doctrine.documentmanager.odm_default',
            ],
            'extensionConfigs' => [
//                'Sds\DoctrineExtensions\AccessControl' => null,
//                'Sds\DoctrineExtensions\Accessor' => null,
//                'Sds\DoctrineExtensions\Annotation' => null,
//                'Sds\DoctrineExtensions\Audit' => null,
//                'Sds\DoctrineExtensions\Crypt' => null,
//                'Sds\DoctrineExtensions\Dojo' => [
//                    'destPaths' => [
//                        'all' => [
//                            'filter' => '',
//                            'path' => 'public/js/dojo_src'
//                        ],
//                    ],
//                ],
//                'Sds\DoctrineExtensions\DoNotHardDelete' => null,
//                'Sds\DoctrineExtensions\Freeze' => null,
//                'Sds\DoctrineExtensions\Readonly' => null,
//                'Sds\DoctrineExtensions\Rest' => null,
//                'Sds\DoctrineExtensions\Serializer' => null,
//                'Sds\DoctrineExtensions\SoftDelete' => null,
//                'Sds\DoctrineExtensions\Stamp' => null,
//                'Sds\DoctrineExtensions\State' => null,
//                'Sds\DoctrineExtensions\UiHints' => null,
//                'Sds\DoctrineExtensions\Validator' => null,
//                'Sds\DoctrineExtensions\Workflow' => null,
//                'Sds\DoctrineExtensions\Zone' => null,
            ],
        ],
    ],

    'service_manager' => [
        'invokables' => [
            'Sds\DoctrineExtensions\DocumentValidator' => 'Sds\DoctrineExtensions\Validator\DocumentValidator',
        ],
        'factories' => [
            'Sds\DoctrineExtensions\Serializer' => 'Sds\DoctrineExtensionsModule\Service\SerializerFactory',
        ],
    ],

    'controllers' => [
        'factories' => [
            'Sds\DoctrineExtensionsModule\Controller\JsonRestfulController' => function($serviceLocator){
                return new Sds\DoctrineExtensionsModule\Controller\JsonRestfulController(
                    ['restEndpoint' => $serviceLocator->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch()->getParam('restEndpoint')]
                );
            },
        ],
    ],

    'router' => [
        'routes' => [
            // Rest api route
            'Sds\Zf2ExtensionsModule\RestRoute' => [
                'options' => [
                    'defaults' => [
                        'controller' => 'Sds\DoctrineExtensionsModule\Controller\JsonRestfulController'
                    ],
                ],
            ],
        ],
    ],
];