<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Sds\DoctrineExtensions\Manifest;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Zend\StdLib\ArrayUtils;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Module
{

    public function init(ModuleManager $moduleManager) {

        $eventManager = $moduleManager->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();

        $eventManager->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'onConfigMerge'), 1);
        $sharedEventManager->attach('doctrine', 'loadCli.post', array($this, 'initializeConsole'));
    }

    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attachAggregate(new RouteListener);
    }

    /**
     * Initializes the console with additional commands from the ODM
     *
     * @param \Zend\EventManager\EventInterface $event
     *
     * @return void
     */
    public function initializeConsole(EventInterface $event)
    {
        /* @var $cli \Symfony\Component\Console\Application */
        $cli = $event->getTarget();

        $manifest = $serviceLocator = $event->getParam('ServiceManager')->get('doctrineExtensions.manifest');
        $cli->addCommands($manifest->getCliCommands());

        $helperSet = $cli->getHelperSet();
        foreach ($this->manifest->getCliHelpers() as $key => $helper) {
            $helperSet->set($helper, $key);
        }
    }

    /**
     *
     * @param \Zend\EventManager\Event $event
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

    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleDependencies()
    {
        return [
            'Sds\ExceptionModule',
            'DoctrineMongoODMModule'
        ];
    }
}
