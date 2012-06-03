<?php

namespace SdsDoctrineExtensionsModule\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SdsCommon\ActiveUser\ActiveUserInterface;
use SdsDoctrineExtensions\Common\AnnotationReaderInterface;

class ListenerFactory implements AbstractFactoryInterface
{
    protected $cNames;
    protected $serviceLocator;
    protected $config;
    
    public function canCreateServiceWithName($name) {
    }
    
    protected function getConfig(){
        if(!isset($this->config)){
            $config = $this->serviceLocator->get('Configuration');
            $this->config = $config['sdsDoctrineExtensions'];             
        }
        return $this->config;
    }
    
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name) {
        $this->serviceLocator = $serviceLocator;
        
        $class = $this->checkName((string)$name);  
        if($class){
            $config = $this->getConfig();        
            $instance = new $class();
            if($instance instanceof ActiveUserInterface){
                $instance->setActiveUser($serviceLocator->get($config['activeUser']));
            }            
            if($instance instanceof AnnotationReaderInterface){
                $instance->setReader($serviceLocator->get($config['reader']));
            }              
            return $instance;
        }        
    } 

    protected function checkName($name){
        if(!$this->cNames){
            $this->cNames = array();            
            $config = $this->getConfig();
            foreach($config['subscribers'] as $subscriberClass){
                $this->cNames[$this->canonicalizeName($subscriberClass)] = $subscriberClass;
            }
        }
        if(isset($this->cNames[$name])){
            return $this->cNames[$name];
        }
    }
    
    protected function canonicalizeName($name)
    {
        return strtolower(str_replace(array('-', '_', ' ', '\\', '/'), '', $name));
    }    
}
