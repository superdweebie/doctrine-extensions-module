<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Service;

use Sds\DoctrineExtensions\Manifest;
use Sds\DoctrineExtensionsModule\ManifestAwareInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DoctrineExtensionsServiceAbstractFactory implements AbstractFactoryInterface
{

    protected $manifestServiceManagers = [];

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){
        if ($factoryMapping = $this->getFactoryMapping($name)){
            if ($manifestServiceManager = $this->getManifestServiceManager($factoryMapping['manifestName'], $serviceLocator)){
                if ($factoryMapping['serviceName'] == 'servicemanager'){
                    return true;
                }
                return $manifestServiceManager->has($factoryMapping['serviceName']);
            }
        }
        return false;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){

        $factoryMapping = $this->getFactoryMapping($name);

        $manifestServiceManager = $this->getManifestServiceManager($factoryMapping['manifestName'], $serviceLocator);
        if ($factoryMapping['serviceName'] == 'servicemanager'){
            return $manifestServiceManager;
        }
        $instance = $manifestServiceManager->get($factoryMapping['serviceName']);
        if ($instance instanceof ManifestAwareInterface){
            $instance->setManifestName($factoryMapping['manifestName']);
            $instance->setManifest($manifestServiceManager->get('manifest'));
        }
        return $instance;
    }

    protected function getFactoryMapping($name){

        $matches = [];

        if (! preg_match('/^doctrineextensions\.(?<manifestName>[a-z0-9_]+)\.(?<serviceName>[a-z0-9_.]+)$/', $name, $matches)) {
            return false;
        }

        return [
            'manifestName' => $matches['manifestName'],
            'serviceName' => $matches['serviceName']
        ];
    }

    protected function getManifestServiceManager($manifestName, $serviceLocator){
        if (!isset($this->manifestServiceManagers[$manifestName])){
            $config = $serviceLocator->get('config')['sds']['doctrineExtensions']['manifest'];
            if (isset($config[$manifestName])){
                $manifestServiceManager = Manifest::createServiceManager($config[$manifestName]['service_manager_config']);
                $manifestServiceManager->setService('manifest', new Manifest($config[$manifestName]));
                $manifestServiceManager->addPeeringServiceManager($serviceLocator);
                $this->manifestServiceManagers[$manifestName] = $manifestServiceManager;
            } else {
                return null;
            }
        }
        return $this->manifestServiceManagers[$manifestName];
    }
}
