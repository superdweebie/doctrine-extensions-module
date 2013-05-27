<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Delegator;

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
class EventManagerDelegatorFactory implements DelegatorFactoryInterface, ManifestAwareInterface
{

    use ManifestAwareTrait;

    protected $eventManagers = [];

    public function createDelegatorWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName, $callback)
    {

        if (isset($this->eventManagers[$name])){
            return $this->eventManagers[$name];
        } else {
            $this->eventManagers[$name] = call_user_func($callback);
            $manifestServiceManager = $serviceLocator->get('doctrineExtensions.' . $this->manifestName . '.serviceManager');
            foreach ($this->manifest->getSubscribers() as $subscriber){
                $this->eventManagers[$name]->addEventSubscriber($manifestServiceManager->get($subscriber));
            }
        }

        return $this->eventManagers[$name];
    }
}