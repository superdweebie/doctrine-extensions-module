<?php

namespace SdsDoctrineExtensionsModule;

use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\Event;
use SdsDoctrineExtensions\Serializer\SerializerService;
use SdsInitalizerModule\Service\Events as InitalizerEvents;

class Module
{
    
    public function init(ModuleManager $moduleManager){
        $sharedEvents = $moduleManager->events()->getSharedManager();
        $sharedEvents->attach(
            InitalizerEvents::IDENTIFIER, 
            InitalizerEvents::LOAD_CONTROLLER_LOADER_INITALIZERS, 
            array($this, 'loadInitalizers')
        );
        $sharedEvents->attach(
            InitalizerEvents::IDENTIFIER, 
            InitalizerEvents::LOAD_SERVICE_MANAGER_INITALIZERS, 
            array($this, 'loadInitalizers')
        );        
    }
    
    public function loadInitalizers(Event $event){
        $serviceLocator = $event->getTarget();
        $config = $serviceLocator->get('Configuration');
        $config = $config['sdsDoctrineExtensions'];
        return array(
            'AnnotationReaderAwareInterface' =>
                function ($instance) use ($serviceLocator, $config) {
                    if ($instance instanceof AnnotationReaderAwareInterface) {
                        $instance->setAnnotationReader($serviceLocator->get($config['annotationReader']));
                    }
                },
            'ActiveUserAwareInterface' =>
                function ($instance) use ($serviceLocator, $config) {
                    if ($instance instanceof ActiveUserAwareInterface) {
                        $instance->setActiveUser($serviceLocator->get($config['activeUser']));
                    }
                }                
        );
    }    
    
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function onBootstrap(Event $e){       
        $app = $e->getParam('application');        
        $serviceLocator = $app->getServiceManager();
        $documentManager = $serviceLocator->get('mongo_dm');
        
        $serializerService = SerializerService::getInstance();
        $serializerService->setDocumentManager($documentManager);                  
        
        $config = $serviceLocator->get('Configuration');
        $config = $config['sdsDoctrineExtensions'];        
        $activeUser = $serviceLocator->get($config['activeUser']);
        $filter = $documentManager->getFilters()->enable('readAccessControl');        
        $filter->setParameter('activeUser', $activeUser);
    }        
       
//    public function getServiceConfiguration()
//    {
//        return array(
//            'abstract_factories' => array(
//                'SdsDoctrineExtensions\Common\Listener\AbstractListener' => 'SdsDoctrineExtensionsModule\Service\ListenerFactory',
//            )
//        );
//    }  
}