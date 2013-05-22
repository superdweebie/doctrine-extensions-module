<?php
return [
    'sds' => [
        'doctrineExtensions' => [

            //doctrineExtensions supports multiple manifest-documentManager pairs.
            //each manifest should be configured with it's own documentManager.
            //a default manifest is pre-configured with the default documentManager
            'manifest' => [
                'default' => [
                    'document_manager' => 'doctrine.odm.documentmanager.default',
                    'extension_configs' => [
//                        'extension.accessControl' => true,
//                        'extension.annotation' => true,
//                        'extension.crypt' => true,
//                        'extension.dojo' => true,
//                        'extension.freeze' => true,
//                        'extension.generator' => true,
//                        'extension.identity' => true,
//                        'extension.owner' => true,
//                        'extension.readonly' => true,
//                        'extension.reference' => true,
//                        'extension.rest' => true,
//                        'extension.serializer' => true,
//                        'extension.softdelete' => true,
//                        'extension.stamp' => true,
//                        'extension.state' => true,
//                        'extension.validator' => true,
//                        'extension.zone' => true,
                    ],
                    'service_manager_config' => [
                        'invokables' => [
                            'eventManagerDelegatorFactory' => 'Sds\DoctrineExtensionsModule\Delegator\EventManagerDelegatorFactory',
                            'configurationDelegatorFactory' => 'Sds\DoctrineExtensionsModule\Delegator\ConfigurationDelegatorFactory'
                        ],
                        'abstract_factories' => [
                            'Sds\DoctrineExtensionsModule\Service\IdentityAbstractFactory'
                        ]
                    ]
                ]
            ]
        ],
        'exception' => [
            'exception_map' => [
                'Sds\DoctrineExtensionsModule\Exception\FlushException' => [
                    'described_by' => 'flush-exception',
                    'title' => 'Exception occured when writing data to the database',
                    'extension_fields' => ['innerExceptions']
                ],
                'Sds\DoctrineExtensionsModule\Exception\DocumentNotFoundException' => [
                    'described_by' => 'document-not-found',
                    'title' => 'Document not found',
                    'status_code' => 404
                ],
                'Sds\DoctrineExtensionsModule\Exception\BadRangeException' => [
                    'described_by' => 'bad-range',
                    'title' => 'Requested range cannot be returned',
                    'status_code' => 416
                ],
                'Sds\DoctrineExtensionsModule\Exception\InvalidDocumentException' => [
                    'described_by' => 'document-validation-failed',
                    'title' => 'Document validation failed',
                    'extension_fields' => ['validatorMessages']
                ],
                'Sds\DoctrineExtensionsModule\Exception\DocumentAlreadyExistsException' => [
                    'described_by' => 'document-already-exists',
                    'title' => 'Document already exists'
                ],
                'Sds\DoctrineExtensionsModule\Exception\AccessControlException' => [
                    'described_by' => 'access-control-exception',
                    'title' => 'Access denied',
                    'status_code' => 403,
                    'action' => ['action']
                ]
            ]
        ]
    ],

    'doctrine' => [
        'odm' => [
            'configuration' => [
                'default' => [
                    'classMetadataFactoryName' => 'Sds\DoctrineExtensions\ClassMetadataFactory'
                ]
            ],
        ]
    ],

    'router' => [
        'routes' => [
            'rest.default' => [
                //this route will look to load a controller
                //service called `rest.<manifestName>.<endpoint>`
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/rest/:endpoint[/:id]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'         => '[a-zA-Z][a-zA-Z0-9/_-]+',
                    ],
                    'defaults' => [
                        'extension'    => 'rest',
                        'manifestName' => 'default',
                    ]
                ],
            ],
            'dojo.default' => [
                //this route will look to load a controller
                //service called `dojo.<manifestName>`
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/dojo/:module',
                    'constraints' => [
                        'module'     => '[a-zA-Z][a-zA-Z0-9/_-]+.js',
                    ],
                    'defaults' => [
                        'extension'    => 'dojo',
                        'manifestName' => 'default',
                        'action'     => 'index',
                    ],
                ],
            ]
        ]
    ],

    'controllers' => [
        'abstract_factories' => [
            'Sds\DoctrineExtensionsModule\Service\BatchRestControllerAbstractFactory',
            'Sds\DoctrineExtensionsModule\Service\RestControllerAbstractFactory',
            'Sds\DoctrineExtensionsModule\Service\DojoControllerAbstractFactory'
        ]
    ],

    'service_manager' => [
        'invokables' => [
            'doctrine.factory.driver' => 'Sds\DoctrineExtensionsModule\Factory\DriverFactory',
        ],
        'abstract_factories' => [
            'Sds\DoctrineExtensionsModule\Service\DoctrineExtensionsServiceAbstractFactory'
        ]
    ],

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    )
];
