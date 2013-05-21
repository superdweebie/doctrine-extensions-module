<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Delegator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Sds\DoctrineExtensionsModule\ManifestAwareInterface;
use Sds\DoctrineExtensionsModule\ManifestAwareTrait;
use Zend\ServiceManager\DelegatorFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class ConfigurationDelegatorFactory implements DelegatorFactoryInterface, ManifestAwareInterface
{

    use ManifestAwareTrait;

    protected $configurations = [];

    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {

        if (isset($this->configurations[$name])){
            return $this->configurations[$name];
        } else {
            $this->configurations[$name] = call_user_func($callback);
            foreach ($this->manifestConfig['filters'] as $filterName => $filterClass){
                $this->configurations[$name]->addFilter($filterName, $filterClass);
            }
            $chain = $this->configurations[$name]->getMetadataDriverImpl();
            foreach ($this->manifestConfig['documents'] as $namespace => $path){
                $driver = new AnnotationDriver(new AnnotationReader, $path);
                $chain->addDriver($driver, $namespace);
            }
        }

        return $this->configurations[$name];
    }
}