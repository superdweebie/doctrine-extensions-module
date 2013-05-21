<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Service;

use Sds\DoctrineExtensionsModule\Controller\DojoController;
use Sds\DoctrineExtensionsModule\Options\DojoController as Options;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DojoControllerAbstractFactory implements AbstractFactoryInterface
{

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName){
        if ($manifestName = $this->getManifestName($name)){
            return true;
        }
        return false;
    }

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $manifestName = $this->getManifestName($name);

        $options = new Options([
            'generator' => 'doctrineExtensions.' . $manifestName . '.generator',
            'documentManager' => $serviceLocator->getServiceLocator()->get('config')['sds']['doctrineExtensions']['manifest'][$manifestName]['document_manager'],
            'serviceLocator' => $serviceLocator->getServiceLocator()->get('doctrineExtensions.' . $manifestName . '.serviceManager')
        ]);

        return new DojoController($options);
    }

    protected function getManifestName($name){

        $matches = [];

        if (! preg_match('/^dojo\.(?<manifestName>[a-z0-9_]+)$/', $name, $matches)) {
            return false;
        }

        return $matches['manifestName'];
    }
}
