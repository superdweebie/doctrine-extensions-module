<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Service;

use Doctrine\Common\Annotations;
use Sds\DoctrineExtensions\Manifest;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ManifestFactory extends FactoryInterface {

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \SdsDoctrineExtensions\Manifest
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Configuration');

        if (isset($config['doctrine'][$config['sdsDoctrineExtensions']['doctrine']['configuration']]['metadataCache'])){
            $cacheName = 'doctrine.cache'.$config['doctrine'][$config['sdsDoctrineExtensions']['doctrine']['configuration']]['metadataCache'];
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
            'ExtensionConfigs' => $config['sdsDoctrineExtensions']['extensionConfigs']
        );

        if (isset($config['sdsDoctrineExtensions']['activeUser'])) {
            if (is_string($config['sdsDoctrineExtensions']['activeUser'])) {
                $manifestConfig['activeUser'] = $serviceLocator->get($config['sdsDoctrineExtensions']['activeUser']);
            } else {
                $manifestConfig['activeUser'] = $config['sdsDoctrineExtensions']['activeUser'];
            }
        }

        return new Manifest($manifestConfig);
    }
}