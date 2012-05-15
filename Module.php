<?php

namespace SdsDoctrineExtensionsModule;

use Zend\Module\Manager,
    Zend\Module\Consumer\AutoloaderProvider,
    Doctrine\Common\Annotations\AnnotationRegistry;

class Module implements AutoloaderProvider
{
    public function init(Manager $moduleManager)
    {
        $moduleManager->events()->attach('loadModules.post', array($this, 'modulesLoaded'));           
    }
      
    public function getAutoloaderConfig()
    {
        return array();        
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
     
    public function modulesLoaded($e)
    {
        $annotationReflection = new \ReflectionClass('SdsDoctrineExtensions\ODM\MongoDB\Mapping\Annotation\Audited');
        $path = dirname($annotationReflection->getFileName());        
        AnnotationRegistry::registerAutoloadNamespace(
            'SdsDoctrineExtensions\ODM\MongoDB\Mapping\Annotation', 
            $path
        );           
    }    
}