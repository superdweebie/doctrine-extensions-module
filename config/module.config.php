<?php
return array(   
    'di' => array(
        'instance' => array(
            
            'mongo_evm' => array(
                'injections' => array(
                    'setSubscriber' => array(
                        array('subscriber' => 'SdsDoctrineExtensions\ActiveUser\Listener\ActiveUser'),
                        array('subscriber' => 'SdsDoctrineExtensions\Audit\Listener\Audit'),
                        array('subscriber' => 'SdsDoctrineExtensions\Readonly\Listener\Readonly'),                    
                        array('subscriber' => 'SdsDoctrineExtensions\SoftDelete\Listener\SoftDelete'),                          
                        array('subscriber' => 'SdsDoctrineExtensions\Serializer\Listener\Serializer'),                          
                    )
                ),
            ),
                                  
            'SdsDoctrineExtensions\ActiveUser\Listener\ActiveUser' => array(
              'parameters' => array(
                  'activeUser' => 'active_user',
              ),  
            ),

            'SdsDoctrineExtensions\Audit\Listener\Audit' => array(
              'parameters' => array(
                  'activeUser' => 'active_user',
                  'driverChain' => 'mongo_driver_chain',                  
              ),  
            ),            
            
            'SdsDoctrineExtensions\Readonly\Listener\Readonly' => array(
              'parameters' => array(
                  'driverChain' => 'mongo_driver_chain',                  
              ),  
            ), 
            
            'SdsDoctrineExtensions\Serializer\Listener\Serializer' => array(
              'parameters' => array(
                  'driverChain' => 'mongo_driver_chain',                  
              ),  
            ), 
                        
            'mongo_driver_chain' => array(
                'parameters' => array(
                    'drivers' => array(
                        'sds_audit_annotation_driver' => array(
                            'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                            'namespace' => 'SdsDoctrineExtensions\Audit\Model',
                            'paths' => array(
                                'vendor/superdweebie/SdsDoctrineExtensions/lib/SdsDoctrineExtensions/Audit/Model'
                            ),                            
                         ),  
                        'sds_accesscontrol_annotation_driver' => array(
                            'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                            'namespace' => 'SdsDoctrineExtensions\AccessControl\Model',
                            'paths' => array(
                                'vendor/superdweebie/SdsDoctrineExtensions/lib/SdsDoctrineExtensions/AccessControl/Model'
                            ),                            
                         ),                         
                     ),
                ),
            ), 
        ),        
        'definition' => array(
            'class' => array(
                'SdsDoctrineExtensionsModule\Factory\Find' => array(
                    'instantiator' => array(
                        'SdsDoctrineExtensionsModule\Factory\Find', 'get',                      
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),
                            'id' => array('type' => false, 'required' => true),                            
                        ),                                             
                    ),
                ),  
                'SdsDoctrineExtensionsModule\Factory\FindAll' => array(
                    'instantiator' => array(
                        'SdsDoctrineExtensionsModule\Factory\FindAll', 'get'                        
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),                         
                        ),                                                
                    ),
                ),                 
                'SdsDoctrineExtensionsModule\Factory\FindBy' => array(
                    'instantiator' => array(
                        'SdsDoctrineExtensionsModule\Factory\FindBy', 'get'                        
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),
                            'criteria' => array('type' => false, 'required' => false),  
                            'orderBy' => array('type' => false, 'required' => false),    
                            'limit' => array('type' => false, 'required' => false),
                            'offset' => array('type' => false, 'required' => false),                                                   
                        ),                                                
                    ),
                ), 
                'SdsDoctrineExtensionsModule\Factory\FindOneBy' => array(
                    'instantiator' => array(
                        'SdsDoctrineExtensionsModule\Factory\FindOneBy', 'get'                        
                    ),
                    'methods' => array(
                        'get' => array(
                            'objectManager' => array('type' => 'Doctrine\Common\Persistence\ObjectManager', 'required' => true),
                            'objectClassName' => array('type' => false, 'required' => true),
                            'criteria' => array('type' => false, 'required' => false),                                                 
                        ),                                                
                    ),
                ), 
            ),
        ),
    ),    
);