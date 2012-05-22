<?php
return array(
    'sds_doctrine_extensions_config' => array(
        'active_user' => 'active_user',
        'reader' => 'Doctrine\Common\Annotations\CachedReader',
        'drivers' => array(
            array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'namespace' => 'SdsDoctrineExtensions\Audit\Model',
                'paths' => array(
                    'vendor/superdweebie/SdsDoctrineExtensions/lib/SdsDoctrineExtensions/Audit/Model'
                ),                            
            ),  
            array(
                'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                'namespace' => 'SdsDoctrineExtensions\AccessControl\Model',
                'paths' => array(
                    'vendor/superdweebie/SdsDoctrineExtensions/lib/SdsDoctrineExtensions/AccessControl/Model'
                ),                         
            ),          
        ),        
        'filters' => array(
            'readAccessControl' => 'SdsDoctrineExtensions\AccessControl\Filter\ReadAccessControl',            
        ),
        'subscribers' => array(
            'SdsDoctrineExtensions\ActiveUser\Listener\ActiveUser',
            'SdsDoctrineExtensions\AccessControl\Listener\AccessControl',                   
            'SdsDoctrineExtensions\Audit\Listener\Audit',
            'SdsDoctrineExtensions\Readonly\Listener\Readonly',                    
            'SdsDoctrineExtensions\SoftDelete\Listener\SoftDelete',                          
            'SdsDoctrineExtensions\Serializer\Listener\Serializer', 
            'SdsDoctrineExtensions\Stamp\Listener\Stamp',             
        )
    ),    
);