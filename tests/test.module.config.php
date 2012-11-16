<?php
return array(
    'sds' => array(
        'doctrineExtensions' => array(
            'extensionConfigs' => array(
                'Sds\DoctrineExtensions\AccessControl' => null,
                'Sds\DoctrineExtensions\Accessor' => null,
                'Sds\DoctrineExtensions\Annotation' => null,
                'Sds\DoctrineExtensions\Audit' => null,
                'Sds\DoctrineExtensions\Crypt' => null,
                'Sds\DoctrineExtensions\DoNotHardDelete' => null,
                'Sds\DoctrineExtensions\Dojo' => array(
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
            ),
            'test' => [
                'jsonRestfulControllerOptions' => [
                    'documentManager' => 'doctrine.documentmanager.odm_default',
                    'documentValidator' => 'Sds\DoctrineExtensions\DocumentValidator',
                    'serializer' => 'Sds\DoctrineExtensions\Serializer',
                    'documentClass' => 'Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Game',
                    'limit' => 30 //max number of records returned from getList
                ]
            ]
        )
    ),
    'doctrine' => array(
        'configuration' => array(
            'odm_default' => array(
                'default_db' => 'doctrineExtensionsModuleTest'
            )
        ),
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
    'controllers' => array(
        'factories' => array(
            'Sds\DoctrineExtensionsModule\Test\TestAsset\JsonRestfulController' => function($serviceLocator){
        return new Sds\DoctrineExtensionsModule\Controller\JsonRestfulController($serviceLocator->getServiceLocator()->get('Config')['sds']['doctrineExtensions']['test']['jsonRestfulControllerOptions']);
            }
        ),
    ),
);
