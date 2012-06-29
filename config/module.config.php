<?php
return array(
    'sdsDoctrineExtensions' => array(
        'activeUser' => 'SdsAuthModule\ActiveUser',
        'annotationReader' => 'Doctrine\Common\Annotations\CachedReader',
        'extensions' => array(
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