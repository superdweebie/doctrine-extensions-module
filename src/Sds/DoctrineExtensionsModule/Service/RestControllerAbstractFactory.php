<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Service;

use Sds\DoctrineExtensionsModule\Controller\JsonRestfulController;
use Sds\DoctrineExtensionsModule\Options\JsonRestfulController as Options;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RestControllerAbstractFactory implements AbstractFactoryInterface
{

    protected $endpointMap;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){
        if ($factoryMapping = $this->getFactoryMapping($name)){
            return $this->getEndpointMap($factoryMapping['manifestName'], $serviceLocator)->has($factoryMapping['endpoint']);
        }
        return false;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){

        $factoryMapping = $this->getFactoryMapping($name);

        $options = new Options([
            'endPoint' => $factoryMapping['endpoint'],
            'documentClass' => $this->getEndpointMap($factoryMapping['manifestName'], $serviceLocator)->get($factoryMapping['endpoint'])['className'],
            'documentManager' => $serviceLocator->getServiceLocator()->get('config')['sds']['doctrineExtensions']['manifest'][$factoryMapping['manifestName']]['document_manager'],
            'manifestName' => $factoryMapping['manifestName'],
            'serviceLocator' => $serviceLocator->getServiceLocator()->get('doctrineExtensions.' . $factoryMapping['manifestName'] . '.serviceManager')
        ]);
        return new JsonRestfulController($options);
    }

    protected function getEndpointMap($manifestName, $serviceLocator){
        if (!isset($this->endpointMap)){
            $this->endpointMap = $serviceLocator->getServiceLocator()->get('doctrineExtensions.' . $manifestName . '.endpointMap');
        }
        return $this->endpointMap;
    }

    protected function getFactoryMapping($name){

        $matches = [];

        if (! preg_match('/^rest\.(?<manifestName>[a-z0-9_]+)\.(?<endpoint>[a-z0-9_]+)$/', $name, $matches)) {
            return false;
        }

        return $matches;
    }
}
