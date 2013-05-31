<?php

namespace Sds\DoctrineExtensionsModule\Service;

use DoctrineModule\Options\Cache\FilesystemCacheOptions;
use Sds\DoctrineExtensionsModule\Cache\PhpFileSerializeCache;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PhpFileSerializeCacheFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return Application
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        if (isset($config['doctrine']['cache']['phpfileserialize'])) {
            $options = new FilesystemCacheOptions($config['doctrine']['cache']['phpfileserialize']);
        } else {
            $options = new FilesystemCacheOptions();
        }

        $instance = new PhpFileSerializeCache($options->getDirectory());
        $instance->setNamespace($options->getNamespace());

        return $instance;
    }
}
