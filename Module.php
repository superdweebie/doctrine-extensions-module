<?php

namespace SdsDoctrineExtensionsModule;

use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\Event;
use SdsDoctrineExtensions\Common\AnnotationRegistrator;
use SdsDoctrineExtensions\Serializer\SerializerService;

class Module
{
    public function init(ModuleManager $mm){
        $sharedEvents = $mm->events()->getSharedManager();
        $sharedEvents->attach('DoctrineMongoODMModule', 'loadDrivers', array($this, 'loadMongoODMDrivers'));      
        $sharedEvents->attach('DoctrineMongoODMModule', 'loadFilters', array($this, 'loadMongoODMFilters'));           
        $sharedEvents->attach('DoctrineMongoODMModule', 'loadSubscribers', array($this, 'loadMongoODMSubscribers'));         
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(Event $e){
        $annotationRegistrator = new AnnotationRegistrator;
        $annotationRegistrator->registerAll(); 
        
        $app = $e->getParam('application');        
        $sm = $app->getServiceManager();
        $dm = $sm->get('Doctrine\ODM\MongoDB\DocumentManager');
        
        $serializerService = SerializerService::getInstance();
        $serializerService->setDocumentManager($dm);                  
        
        $activeUser = $sm->get('active_user');
        $filter = $dm->getFilters()->enable('readAccessControl');        
        $filter->setParameter('activeUser', $activeUser);
    }
    
    public function loadMongoODMDrivers($e){
        $serviceLocator = $e->getTarget();
        $reader = $serviceLocator->get('Doctrine\Common\Annotations\CachedReader');
        $config = $serviceLocator->get('Configuration')->sds_doctrine_extensions_config->drivers->toArray();
        $return = array();
        
        foreach($config as $params){
            $return[$params['namespace']] = new $params['class']($reader, $params['paths']);
        }   
        return $return;
    }
    
    public function loadMongoODMFilters($e){
        $sl = $e->getTarget();
        return $sl->get('Configuration')->sds_doctrine_extensions_config->filters->toArray();       
    }
    
    public function loadMongoODMSubscribers($e){
        $sl = $e->getTarget();
        $subscriberClasses = $sl->get('Configuration')->sds_doctrine_extensions_config->subscribers->toArray();
        $subscribers = array();
        foreach($subscriberClasses as $subscriberClass){
            $subscribers[] = $sl->get($subscriberClass);
        }
        return $subscribers;
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