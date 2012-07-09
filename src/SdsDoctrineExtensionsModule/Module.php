<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace SdsDoctrineExtensionsModule;

use SdsDoctrineExtensionsModule\Service\ManifestFactory;
use Zend\EventManager\Event;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Module
{

    /**
     *
     * @param \Zend\EventManager\Event $event
     */
    public function onBootstrap(Event $event)
    {
        $app = $event->getTarget();
        $serviceManager = $app->getServiceManager();

        $manifest = $serviceManager->get('sdsDoctrineExtensions.mainfest');

        $config = $serviceManager->get('Configuration');

        //Inject subscribers
        foreach ($manifest->getSubscribers() as $subscriber) {
            $config['doctrine']['eventmanager'][$config['sdsDoctrineExtensions']['doctrine']['eventmanager']]['subscribers'][] = $subscriber;
        }

        //Inject annotations
        foreach ($manifest->getAnnotations() as $namespace => $path) {
            $config['doctrine'][$config['sdsDoctrineExtensions']['doctrine']['configuration']]['annotations'][$namespace] = $path;
        }

        //Inject filtsers
        foreach ($manifest->getFilters() as $filter) {
            $config['doctrine'][$config['sdsDoctrineExtensions']['doctrine']['configuration']]['filters'][] = $filter;
        }

        //inject document paths
        $config['doctrine']['driver'][$config['sdsDoctrineExtensions']['doctrine']['driver']]['drivers'][] = array(
            'paths' => $manifest->getDocuments()
        );

        $serviceManager->set('Configuration', $config);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     *
     * @return array
     */
    public function getServiceConfiguration()
    {
        return array(
            'factories' => array(
                'sdsDoctrineExtensions.manifest'    => new ManifestFactory()
            )
        );
    }
}