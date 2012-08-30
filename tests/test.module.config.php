<?php
return array(
    'sds' => array(
        'doctrineExtensions' => array(
            'activeUser' => 'testActiveUser',
            'extensionConfigs' => array(
                'Sds\DoctrineExtensions\AccessControl' => null,
                'Sds\DoctrineExtensions\Accessor' => null,
                'Sds\DoctrineExtensions\Annotation' => null,
                'Sds\DoctrineExtensions\Audit' => null,
                'Sds\DoctrineExtensions\Crypt' => null,
                'Sds\DoctrineExtensions\DoNotHardDelete' => null,
                'Sds\DoctrineExtensions\DojoModel' => array(
                    'destPaths' => array(
                        'all' => array(
                            'filter' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document',
                            'path' => 'data'
                        ),
                    ),
                ),
                'Sds\DoctrineExtensions\Freeze' => null,
                'Sds\DoctrineExtensions\Readonly' => null,
                'Sds\DoctrineExtensions\Serializer' => null,
                'Sds\DoctrineExtensions\SoftDelete' => null,
                'Sds\DoctrineExtensions\Stamp' => null,
                'Sds\DoctrineExtensions\State' => null,
                'Sds\DoctrineExtensions\Validator' => null,
                'Sds\DoctrineExtensions\Workflow' => null,
                'Sds\DoctrineExtensions\Zone' => null,
            )
        )
    ),
    'doctrine' => array(
        'driver' => array(
            'odm_default' => array(
                'drivers' => array(
                    'Sds\DoctrineExtensionsModule\Test\TestAsset\Document' => 'test'
                )
            ),
            'test' => array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__.'/Sds/DoctrineExtensionsModule/Test/TestAsset/Document'
                ),
            )
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'testActiveUser' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\ActiveUser'
        ),
    ),
);
