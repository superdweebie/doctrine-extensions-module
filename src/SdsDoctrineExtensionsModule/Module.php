<?php

namespace SdsDoctrineExtensionsModule;

use DoctrineMongoODModule\Events;
use SdsDoctrineExtensions\Manifest;
use SdsDoctrineExtensions\ManifestConfig;
use Zend\ModuleManager\ModuleManager;
use Zend\EventManager\Event;

class Module
{

    protected $manifest;

    public function init(ModuleManager $moduleManager){
        $sharedEvents = $moduleManager->events()->getSharedManager();
        $sharedEvents->attach(
            Events::identifier,
            Events::getSubscribers,
            array($this, 'getSubscribers')
        );
        $sharedEvents->attach(
            Events::identifier,
            Events::getAnnotations,
            array($this, 'getAnnotations')
        );
        $sharedEvents->attach(
            Events::identifier,
            Events::getFilters,
            array($this, 'getFilters')
        );
    }

    public function getManifest(ServiceLocator $serviceLocator){
        if (!isset($this->manifest)) {
            $config = $serviceLocator->get('Configuration')['sdsDoctrineExtensions'];
            $activeUser = $serviceLocator->get($config['activeUser']);
            $annotationReader = $serviceLocator->get($config['annotationReader']);

            $manifestConfig = new ManifestConfig($annotationReader, null, $activeUser);

            foreach ($config['extensions'] as $namespace => $extensionConfigArray) {
                $extensionConfigClass = $namespace . '\ExtensionConfig';
                $extensionConfig = new $extensionConfigClass($extensionConfigArray);
                $manifestConfig->addExtensionConfig($namespace, $extensionConfig);
            }

            $this->manifest = new Manifest($manifestConfig);
        }
        return $this->manifest;
    }

    public function getSubscribers(Event $event){
        return $this->getManifest($event->getTarget())->getSubscribers();
    }

    public function getAnnotations(Event $event){
        return $this->getManifest($event->getTarget())->getAnnotations();
    }

    public function getFilters(Event $event){
        return $this->getManifest($event->getTarget())->getFilters();
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}