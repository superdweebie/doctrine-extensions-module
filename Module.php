<?php

namespace SdsDoctrineExtensionsModule;

use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\Event;
use SdsDoctrineExtensions\Common\AnnotationRegistrator;
use SdsDoctrineExtensions\Serializer\SerializerService;

class Module
{
    public function init(ModuleManager $moduleManager){
        $sharedEvents = $moduleManager->events()->getSharedManager();
        $sharedEvents->attach('DoctrineMongoODMModule', 'loadDrivers', array($this, 'loadMongoODMDrivers'));      
        $sharedEvents->attach('DoctrineMongoODMModule', 'loadFilters', array($this, 'loadMongoODMFilters'));           
        $sharedEvents->attach('DoctrineMongoODMModule', 'loadSubscribers', array($this, 'loadMongoODMSubscribers'));         
        $sharedEvents->attach('DoctrineMongoODMModule', 'loadAnnotations', array($this, 'loadMongoODMAnnotations'));         
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(Event $e){       
        $app = $e->getParam('application');        
        $sm = $app->getServiceManager();
        $dm = $sm->get('mongo_dm');
        
        $serializerService = SerializerService::getInstance();
        $serializerService->setDocumentManager($dm);                  
        
        $activeUser = $sm->get('active_user');
        $filter = $dm->getFilters()->enable('readAccessControl');        
        $filter->setParameter('activeUser', $activeUser);
    }
    
    public function loadMongoODMDrivers($e){
        $serviceLocator = $e->getTarget();
        $reader = $serviceLocator->get('Doctrine\Common\Annotations\CachedReader');
        $config = $serviceLocator->get('Configuration')['sds_doctrine_extensions_config']['drivers'];
        $return = array();
        
        foreach($config as $params){
            $return[$params['namespace']] = new $params['class']($reader, $params['paths']);
        }   
        return $return;
    }
    
    public function loadMongoODMFilters($e){
        $serviceLocator = $e->getTarget();
        return $serviceLocator->get('Configuration')['sds_doctrine_extensions_config']['filters'];       
    }
    
    public function loadMongoODMSubscribers($e){
        $serviceLocator = $e->getTarget();
        $subscriberClasses = $serviceLocator->get('Configuration')['sds_doctrine_extensions_config']['subscribers'];
        $subscribers = array();
        foreach($subscriberClasses as $subscriberClass){
            $subscribers[] = $serviceLocator->get($subscriberClass);
        }
        return $subscribers;
    }

    public function loadMongoODMAnnotations($e){
        $serviceLocator = $e->getTarget();
        return $serviceLocator->get('Configuration')['sds_doctrine_extensions_config']['annnotations'];
    }
    
    public function getServiceConfiguration()
    {
        return array(
            'abstract_factories' => array(
                'SdsDoctrineExtensions\Common\Listener\AbstractListener' => 'SdsDoctrineExtensionsModule\Service\ListenerFactory',
            )
        );
    }  
}