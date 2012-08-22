<?php
return array(
    'sds' => array(
        'doctrineExtensions' => array(
            'doctrine' => array(
                'driver' => 'odm_default',
                'eventmanager' => 'odm_default',
                'configuration' => 'odm_default',
                'documentManager' => 'doctrine.documentmanager.odm_default',
            ),
            'activeUser' => 'sds.auth.activeUser',
            'renderFlushListener' => false,
            'extensionConfigs' => array(
//                'Sds\DoctrineExtensions\AccessControl' => null,
//                'Sds\DoctrineExtensions\Accessor' => null,
//                'Sds\DoctrineExtensions\Annotation' => null,
//                'Sds\DoctrineExtensions\Audit' => null,
//                'Sds\DoctrineExtensions\Crypt' => null,
//                'Sds\DoctrineExtensions\DojoModel' => array(
//                    'destPaths' => array(
//                        'all' => array(
//                            'filter' => '',
//                            'path' => 'public/js/dojo_src'
//                        ),
//                    ),
//                ),
//                'Sds\DoctrineExtensions\DoNotHardDelete' => null,
//                'Sds\DoctrineExtensions\Freeze' => null,
//                'Sds\DoctrineExtensions\Readonly' => null,
//                'Sds\DoctrineExtensions\Serializer' => null,
//                'Sds\DoctrineExtensions\SoftDelete' => null,
//                'Sds\DoctrineExtensions\Stamp' => null,
//                'Sds\DoctrineExtensions\State' => null,
//                'Sds\DoctrineExtensions\UiHints' => null,
//                'Sds\DoctrineExtensions\Validator' => null,
//                'Sds\DoctrineExtensions\Workflow' => null,
//                'Sds\DoctrineExtensions\Zone' => null,
            ),
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'sds.doctrineExtensions.validator' => 'Sds\DoctrineExtensions\Validator\DocumentValidator',
        ),
        'factories' => array(
            'sds.doctrineExtensions.serializer' => 'Sds\DoctrineExtensionsModule\Service\SerializerFactory',
            'sds.doctrineExtensions.renderFlushListener' => 'Sds\DoctrineExtensionsModule\Service\RenderFlushListenerFactory',
        )
    ),
);