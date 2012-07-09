<?php
return array(
    'sdsDoctrineExtensions' => array(
        'doctrine' => array(
            'driver' => 'odm_default',
            'eventmanager' => 'odm_default',
            'configuration' => 'odm_default',
        ),
        'activeUser' => 'sdsAuthModule.activeUser',
        'extensionConfigs' => array(
            'SdsDoctrineExtensions\AccessControl' => null,
            'SdsDoctrineExtensions\Audit' => null,
            'SdsDoctrineExtensions\DoNotHardDelete' => null,
            'SdsDoctrineExtensions\Freeze' => null,
            'SdsDoctrineExtensions\Readonly' => null,
            'SdsDoctrineExtensions\Serializer' => null,
            'SdsDoctrineExtensions\SoftDelete' => null,
            'SdsDoctrineExtensions\Stamp' => null,
            'SdsDoctrineExtensions\State' => null,
            'SdsDoctrineExtensions\UiHints' => null,
            'SdsDoctrineExtensions\Workflow' => null,
            'SdsDoctrineExtensions\Zone' => null,
        ),
    ),
);