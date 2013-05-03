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
//                'Sds\DoctrineExtensions\AccessControl' => true,
//                'Sds\DoctrineExtensions\Annotation' => true,
//                'Sds\DoctrineExtensions\Crypt' => true,
//                'Sds\DoctrineExtensions\Dojo' => true,
//                'Sds\DoctrineExtensions\Freeze' => true,
//                'Sds\DoctrineExtensions\Readonly' => true,
//                'Sds\DoctrineExtensions\Rest' => true,
//                'Sds\DoctrineExtensions\Serializer' => true,
//                'Sds\DoctrineExtensions\SoftDelete' => true,
//                'Sds\DoctrineExtensions\Stamp' => true,
//                'Sds\DoctrineExtensions\State' => true,
//                'Sds\DoctrineExtensions\Validator' => true,
//                'Sds\DoctrineExtensions\Zone' => true,
            ],
        ],
        'exception' => [
            'exceptionMap' => [
                'Sds\DoctrineExtensionsModule\Exception\FlushException' => [
                    'describedBy' => 'flush-exception',
                    'title' => 'Exception occured when writing data to the database',
                    'extensionFields' => ['innerExceptions']
                ],
                'Sds\DoctrineExtensionsModule\Exception\DocumentNotFoundException' => [
                    'describedBy' => 'document-not-found',
                    'title' => 'Document not found',
                    'httpStatus' => 404
                ],
                'Sds\DoctrineExtensionsModule\Exception\BadRangeException' => [
                    'describedBy' => 'bad-range',
                    'title' => 'Requested range cannot be returned',
                    'httpStatus' => 416
                ],
                'Sds\DoctrineExtensionsModule\Exception\InvalidDocumentException' => [
                    'describedBy' => 'document-validation-failed',
                    'title' => 'Document validation failed',
                    'extensionFields' => ['validatorMessages']
                ],
                'Sds\DoctrineExtensionsModule\Exception\DocumentAlreadyExistsException' => [
                    'describedBy' => 'document-already-exists',
                    'title' => 'Document already exists'
                ],
                'Sds\DoctrineExtensionsModule\Exception\AccessControlException' => [
                    'describedBy' => 'access-control-exception',
                    'title' => 'Access denied',
                    'httpStatus' => 403,
                    'action' => ['action']
                ]
            ]
        ]
    ],

    'doctrine' => [
        'configuration' => [
            'odm_default' => [
                'classMetadataFactoryName' => 'Sds\DoctrineExtensions\ClassMetadataFactory'
            ]
        ],
    ],

    'router' => [
        'routes' => [
            'rest' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route' => '/rest/:controller[/:id]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'         => '[a-zA-Z][a-zA-Z0-9/_-]+',
                    ],
                ],
            ],
            'dojo_src' => [
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => [
                    'route'    => '/dojo_src/:module',
                    'constraints' => [
                        'module'     => '[a-zA-Z][a-zA-Z0-9/_-]+.js',
                    ],
                    'defaults' => [
                        'controller' => 'Sds\DoctrineExtensionsModule\Controller\DojoSrcController',
                        'action'     => 'index',
                    ],
                ],
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            'Sds\DoctrineExtensionsModule\Controller\DojoSrcController' => 'Sds\DoctrineExtensionsModule\Service\DojoSrcControllerFactory',
            'batch' => 'Sds\DoctrineExtensionsModule\Service\BatchJsonRestfulControllerFactory',
        ],
        'abstract_factories' => [
            'Sds\DoctrineExtensionsModule\Service\RestControllerFactory'
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
