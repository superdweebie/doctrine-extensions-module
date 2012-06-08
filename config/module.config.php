<?php
return array(
    'sdsDoctrineExtensions' => array(        
        'activeUser' => 'SdsAuthModule\ActiveUser',
        'annotationReader' => 'Doctrine\Common\Annotations\CachedReader'
    ),
    'doctrine' => array(
        'drivers' => array(
            'odm' => array(
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
            )
        ),
        'filters' => array(
            'odm' => array(
                'readAccessControl' => 'SdsDoctrineExtensions\AccessControl\Filter\ReadAccessControl',                
            )
        ),
        'subscribers' => array(
            'odm' => array(
                'SdsDoctrineExtensions\ActiveUser\Listener\ActiveUser',
                'SdsDoctrineExtensions\AccessControl\Listener\AccessControl',                   
                'SdsDoctrineExtensions\Audit\Listener\Audit',
                'SdsDoctrineExtensions\Readonly\Listener\Readonly',                    
                'SdsDoctrineExtensions\SoftDelete\Listener\SoftDelete',                          
                'SdsDoctrineExtensions\Serializer\Listener\Serializer', 
                'SdsDoctrineExtensions\Stamp\Listener\Stamp',             
                'SdsDoctrineExtensions\Metadata\Listener\Metadata',                  
            )
        ),
        'annotations' => array(
            'odm' => array(
                'vendor\superdweebie\SdsDoctrineExtensions\lib\SdsDoctrineExtensions\Audit\Mapping\Annotation\Audit.php',
                'vendor\superdweebie\SdsDoctrineExtensions\lib\SdsDoctrineExtensions\Readonly\Mapping\Annotation\Readonly.php',
                'vendor\superdweebie\SdsDoctrineExtensions\lib\SdsDoctrineExtensions\Serializer\Mapping\Annotation\DoNotserialize.php',
                'vendor\superdweebie\SdsDoctrineExtensions\lib\SdsDoctrineExtensions\Metadata\Mapping\Annotation\Label.php',     
                'vendor\superdweebie\SdsDoctrineExtensions\lib\SdsDoctrineExtensions\Metadata\Mapping\Annotation\Width.php',  
                'vendor\superdweebie\SdsDoctrineExtensions\lib\SdsDoctrineExtensions\Metadata\Mapping\Annotation\Hidden.php',      
            )
        )    
    ),    
);