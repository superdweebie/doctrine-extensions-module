<?php
return [
    'sds' => [
        'doctrineExtensions' => [
            'manifest' => [
                'default' => [
                    'extension_configs' => [
                        'extension.accessControl' => true,
                        'extension.annotation' => true,
                        'extension.crypt' => true,
                        'extension.dojo' => [
                            'flat_file_strategy' => 'ignore'
                        ],
                        'extension.freeze' => true,
                        'extension.generator' => [
                            'resource_map' => [
                                'Sds/Document/Author.js' => [
                                    'generator' => 'generator.dojo.model',
                                    'class'     => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Author'
                                ],
                            ]
                        ],
                        'extension.identity' => true,
                        'extension.owner' => true,
                        'extension.reference' => true,
                        'extension.rest' => [
                            'endpoint_map' => [
                                'game' => [
                                    'class' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game',
                                    'property' => 'name',
                                    'cache_control' => [
                                        'no_cache' => true
                                    ],
                                    'embedded_lists' => [
                                        'components' => [
                                            'property' => 'name',
                                            'class' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Component',
                                            'embedded_lists' => [
                                                'manufacturers' => [
                                                    'property' => 'name',
                                                    'class' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Manufacturer',
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                'author'  => [
                                    'class' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Author',
                                    'property' => 'name'
                                ],
                                'country' => [
                                    'class' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Country',
                                    'property' => 'name'
                                ],
                                'review'  => [
                                    'class' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Review',
                                    'property' => 'title'
                                ],
                                'user'    => [
                                    'class' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\User',
                                    'property' => 'username'
                                ]
                            ]
                        ],
                        'extension.serializer' => [
                            'maxNestingDepth' => 2
                        ],
                        'extension.softdelete' => true,
                        'extension.stamp' => true,
                        'extension.state' => true,
                        'extension.validator' => true,
                        'extension.zone' => true,
                    ],
                    'documents' => [
                        'Sds\DoctrineExtensionsModule\Test\TestAsset\Document' => __DIR__.'/Sds/DoctrineExtensionsModule/Test/TestAsset/Document'
                    ]
                ]
            ],
        ],
    ],

    'doctrine' => [
        'odm' => [
            'configuration' => [
                'default' => [
                    'default_db' => 'doctrineExtensionsModuleTest',
                    'proxy_dir'    => __DIR__ . '/Proxy',
                    'hydrator_dir' => __DIR__ . '/Hydrator',
                ],
            ],
        ],
    ],

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/view/layout/layout.phtml',
            'error/404' => __DIR__ . '/view/error/404.phtml',
            'error/index' => __DIR__ . '/view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/view',
        ),
    ),
];
