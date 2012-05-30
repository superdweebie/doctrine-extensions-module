<?php

namespace SdsDoctrineExtensionsModule\Service;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SdsCommon\User\ActiveUserInterface;
use SdsDoctrineExtensions\Common\AnnotationReaderInterface;

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
            $config = $serviceLocator->get('Configuration')['sdsDoctrineExtensions'];          
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

    protected function checkName(ServiceLocatorInterface $serviceLocator, $name){
        if(!$this->cNames){
            $this->cNames = array();
            $subscriberClasses = $serviceLocator->get('Configuration')['sdsDoctrineExtensions']['subscribers']; 
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
