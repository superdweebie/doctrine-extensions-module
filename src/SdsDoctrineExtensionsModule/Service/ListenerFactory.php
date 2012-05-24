<?php

namespace SdsDoctrineExtensionsModule\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SdsDoctrineExtensions\Common\Utils;

class ListenerFactory implements AbstractFactoryInterface
{
    protected $cNames;
    
    protected $activeUserTrait = 'SdsDoctrineExtensions\ActiveUser\Behaviour\ActiveUser';
    protected $readerTrait = 'SdsDoctrineExtensions\Common\Behaviour\AnnotationReader';
    
    public function canCreateServiceWithName($name) {
    }
    
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name) {
        $class = $this->checkName($serviceLocator, $name);  
        if($class){
            $config = $serviceLocator->get('Configuration')['sds_doctrine_extensions_config'];          
            $instance = new $class();
            if(Utils::checkForTrait($instance, $this->activeUserTrait)){
                $instance->setActiveUser($serviceLocator->get($config['active_user']));
            }            
            if(Utils::checkForTrait($instance, $this->readerTrait)){
                $instance->setReader($serviceLocator->get($config['reader']));
            }              
            return $instance;
        }        
    } 

    protected function checkName(ServiceLocatorInterface $serviceLocator, $name){
        if(!$this->cNames){
            $this->cNames = array();
            $subscriberClasses = $serviceLocator->get('Configuration')['sds_doctrine_extensions_config']['subscribers']; 
            foreach($subscriberClasses as $subscriberClass){
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
