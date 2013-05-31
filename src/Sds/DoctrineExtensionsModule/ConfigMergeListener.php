<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Sds\DoctrineExtensions\Manifest;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\StdLib\ArrayUtils;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */

class ConfigMergeListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onConfigMerge'), 1);
    }

    /**
     * Detach all our listeners from the event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     *
     * @param \Zend\ModuleManager\ModuleEvent $event
     */
    public function onConfigMerge(ModuleEvent $event) {

        $config = $event->getConfigListener()->getMergedConfig(false);

        foreach($config['sds']['doctrineExtensions']['manifest'] as $name => $manifestConfig){
            if (!isset($manifestConfig['initalized']) || !$manifestConfig['initalized']){
                $manifest = new Manifest($manifestConfig);
                $manifestConfig = $manifest->toArray();
                $config['sds']['doctrineExtensions']['manifest'][$name] = $manifestConfig;

                //alias documentManager

                //add delegators
                $documentManagerConfig = $config;
                foreach(explode('.', $manifestConfig['document_manager']) as $key){
                    $documentManagerConfig = $documentManagerConfig[$key];
                }

                $delegatorConfig = [
                    'delegators' => [
                        $manifestConfig['document_manager'] => ['doctrineExtensions.' . $name . '.documentManagerDelegatorFactory'],
                        $documentManagerConfig['eventmanager'] => ['doctrineExtensions.' . $name . '.eventManagerDelegatorFactory'],
                        $documentManagerConfig['configuration'] => ['doctrineExtensions.' .$name . '.configurationDelegatorFactory']
                    ]
                ];
                $config['service_manager'] = ArrayUtils::merge($config['service_manager'], $delegatorConfig);
            }
        }

        if (!isset($config['sds']['doctrineExtensions']['manifest']['default']) ||
            !isset($config['sds']['doctrineExtensions']['manifest']['default']['extension_configs']['extension.dojo'])
        ) {
            //remove dojo_src.default route if doctrineExtensions.dojo.default is not configured
            unset($config['router']['routes']['dojo.default']);
        }

        if (!isset($config['sds']['doctrineExtensions']['manifest']['default']) ||
            !isset($config['sds']['doctrineExtensions']['manifest']['default']['extension_configs']['extension.rest'])
        ) {
            //remove rest.default route if doctrineExtensions.rest.default is not configured
            unset($config['router']['routes']['rest.default']);
        }

        $event->getConfigListener()->setMergedConfig($config);
    }
}
