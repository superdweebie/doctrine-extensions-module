<?php

namespace SdsDoctrineExtensionsModule;

use Zend\Module\Manager,
    Zend\Module\Consumer\AutoloaderProvider,
    SdsDoctrineExtensions\Common\AnnotationRegistrator,
    SdsDoctrineExtensions\Serializer\SerializerService;

class Module implements AutoloaderProvider
{
    public function init(Manager $moduleManager)
    {   
        $events = $moduleManager->events();
        $sharedEvents = $events->getSharedManager();
        $sharedEvents->attach('bootstrap', 'bootstrap', array($this, 'initialize'));             
    }
      
    public function getAutoloaderConfig()
    {
        return array();        
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
        
    public function initialize($e){
        $annotationRegistrator = new AnnotationRegistrator;
        $annotationRegistrator->registerAll(); 
        
        $app = $e->getParam('application');        
        $locator = $app->getLocator();
        $dm = $locator->get('mongo_dm');
        
        $serializerService = SerializerService::getInstance();
        $serializerService->setDocumentManager($dm);                  
        
        $activeUser = $locator->get('active_user');
        $filter = $dm->getFilters()->enable('readAccessControl');        
        $filter->setParameter('activeUser', $activeUser);
    }
}