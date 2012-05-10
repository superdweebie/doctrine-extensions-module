<?php

namespace SdsDoctrineExtensionsModule;

use Zend\Module\Manager,
    Zend\EventManager\StaticEventManager,
    Zend\Module\Consumer\AutoloaderProvider;

class Module implements AutoloaderProvider
{
    public function init(Manager $moduleManager)
    {
    }
    
    public function getAutoloaderConfig()
    {
//        return array(
//            'Zend\Loader\ClassMapAutoloader' => array(
//                __DIR__ . '/autoload_classmap.php',
//            ),
//            'Zend\Loader\StandardAutoloader' => array(
//                'namespaces' => array(
//                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
//                    __NAMESPACE__ => __DIR__ . '/vendor/SdsDoctrineExtensions/lib/' . __NAMESPACE__,                    
//                ),
//            ),
//        );
        
        if (realpath(__DIR__ . '/vendor/SdsDoctrineExtensions/lib')) {
            return array(
                'Zend\Loader\ClassMapAutoloader' => array(
                    __DIR__ . '/autoload_classmap.php',
                ),
            );
        }

        return array();        
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
      
}