<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Doctrine\Common\Annotations;
use Sds\DoctrineExtensions\Manifest;
use Sds\DoctrineExtensions\ManifestConfig;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Module
{

    public function init(ModuleManager $moduleManager) {
        $eventManager = $moduleManager->getEventManager();
        $eventManager->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'onLoadModulesPost'));
    }

    /**
     *
     * @param \Zend\EventManager\Event $event
     */
    public function onLoadModulesPost(ModuleEvent $event) {

        $serviceLocator = $event->getParam('ServiceManager');
        $config = $serviceLocator->get('configuration');
        $doctrineConfig = $config['doctrine'];
        $extensionsConfig = $config['sds']['doctrineExtensions'];

        if (isset($doctrineConfig[$extensionsConfig['doctrine']['configuration']]['metadataCache'])){
            $cacheName = 'doctrine.cache'.$doctrineConfig[$extensionsConfig['doctrine']['configuration']]['metadataCache'];
        } else {
            $cacheName = 'doctrine.cache.array';
        }

        $reader = new Annotations\AnnotationReader;
        $reader = new Annotations\CachedReader(
            new Annotations\IndexedReader($reader),
            $serviceLocator->get($cacheName)
        );

        $manifestConfig = array(
            'AnnotationReader' => $reader,
            'ExtensionConfigs' => $extensionsConfig['extensionConfigs']
        );

        if (isset($extensionsConfig['activeUser'])) {
            if (is_string($extensionsConfig['activeUser'])) {
                $manifestConfig['activeUser'] = $serviceLocator->get($extensionsConfig['activeUser']);
            } else {
                $manifestConfig['activeUser'] = $extensionsConfig['activeUser'];
            }
        }

        $manifest = new Manifest(new ManifestConfig($manifestConfig));

        //Inject subscribers
        foreach ($manifest->getSubscribers() as $subscriber) {
            $doctrineConfig['eventmanager'][$extensionsConfig['doctrine']['eventmanager']]['subscribers'][] = $subscriber;
        }

        //Inject annotations
        foreach ($manifest->getAnnotations() as $namespace => $path) {
            $doctrineConfig['configuration'][$extensionsConfig['doctrine']['configuration']]['annotations'][$namespace] = $path;
        }

        //Inject filters
        foreach ($manifest->getFilters() as $filter) {
            $doctrineConfig['configuration'][$extensionsConfig['doctrine']['configuration']]['filters'][] = $filter;
        }

        //inject document paths
        $id = 0;
        foreach ($manifest->getDocuments() as $namespace => $path) {
            $name = 'sds.doctrineExtensions.'.$id;
            $doctrineConfig['driver'][$extensionsConfig['doctrine']['driver']]['drivers'][$namespace] = $name;
            $doctrineConfig['driver'][$name] = array(
                'paths' => array($path)
            );
            $id++;
        }

        $config['doctrine'] = $doctrineConfig;
        $serviceLocator->setService('Configuration', $config);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }

}