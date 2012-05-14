<?php
return array(
    'di' => array(
        'instance' => array(

            'mongo_evm' => array(
                'injections' => array(
                    'setSubscriber' => array(
                        array('subscriber' => 'SdsDoctrineExtensions\Listener\ActiveUser')
                    )
                ),
            ),
                                  
            'SdsDoctrineExtensions\Listener\ActiveUser' => array(
              'parameters' => array(
                  'activeUser' => 'active_user',
              ),  
            ),
            
            'mongo_driver_chain' => array(
                'parameters' => array(
                    'drivers' => array(
                        'sdsDoctrineExtensionsModule_annotation_driver' => array(
                            'class'     => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                            'namespace' => 'SdsDoctrineExtensions\Model',
                            'paths'     => array(
                                'vendor/SdsDoctrineExtensionsModule/vendor/SdsDoctrineExtensions/lib/SdsDoctrineExtensions/Model'
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