<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Service;

use Sds\DoctrineExtensionsModule\Controller\BatchJsonRestfulController;
use Sds\DoctrineExtensionsModule\Options\BatchJsonRestfulControllerOptions;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class BatchRestControllerAbstractFactory implements AbstractFactoryInterface
{

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){
        return (bool) $this->getManifestName($name);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){

        $manifestName = $this->getManifestName($name);

        $options = new BatchJsonRestfulControllerOptions([
            'documentManager' => $serviceLocator->getServiceLocator()->get('config')['sds']['doctrineExtensions']['manifest'][$manifestName]['document_manager'],
            'manifestName' => $manifestName,
            'serviceLocator' => $serviceLocator->getServiceLocator()->get('doctrineExtensions.' . $manifestName . '.serviceManager')
        ]);
        return new BatchJsonRestfulController($options);
    }

    protected function getManifestName($name){

        $matches = [];

        if (! preg_match('/^rest\.(?<manifestName>[a-z0-9_]+)\.batch$/', $name, $matches)) {
            return false;
        }

        return $matches['manifestName'];
    }
}
