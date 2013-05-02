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
class RestControllerFactory implements AbstractFactoryInterface
{

    protected $endpointMap;

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){
        return $this->getEndpointMap($serviceLocator)->has($name);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){

        $options = new Options([
            'endPoint' => $name,
            'documentClass' => $this->getEndpointMap($serviceLocator)->get($name)['className']
        ]);
        $options->setServiceLocator($serviceLocator->getServiceLocator());
        $instance = new JsonRestfulController($options);
        $options->getDocumentManager()->getEventManager()->addEventSubscriber($instance);
        return $instance;
    }

    protected function getEndpointMap($serviceLocator){
        if (!isset($this->endpointMap)){
            $this->endpointMap = $serviceLocator->getServiceLocator()->get('Sds\DoctrineExtensions\ServiceManager')->get('endpointMap');
        }
        return $this->endpointMap;
    }
}
