<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Sds\DoctrineExtensions\Manifest;
use Sds\DoctrineExtensions\MasterLazySubscriber;
use Sds\DoctrineExtensions\ServiceManagerFactory;
use Zend\EventManager\Event;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;

/**
 *
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Module
{

    protected $rewriteConfigCache = false;

    public function init(ModuleManager $moduleManager) {
        $listenerOptions = $moduleManager->getEvent()->getConfigListener()->getOptions();
        if ($listenerOptions->getConfigCacheEnabled() &&
            !file_exists($listenerOptions->getConfigCacheFile())
        ) {
            $this->rewriteConfigCache = true;
        }

        $moduleManager
            ->getEventManager()
            ->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, array($this, 'postLoadModules'), -10000);
    }

    /**
     *
     * @param \Zend\EventManager\Event $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        // Attach to helper set event and load the document manager helper.
        $event
            ->getTarget()
            ->getEventManager()
            ->getSharedManager()
            ->attach('doctrine', 'loadCli.post', array($this, 'loadCli'));
    }

    /**
     *
     * @param \Zend\EventManager\Event $event
     */
    public function loadCli(Event $event){
        $cli = $event->getTarget();
        $cli->addCommands($this->manifest->getCliCommands());

        $helperSet = $cli->getHelperSet();
        foreach ($this->manifest->getCliHelpers() as $key => $helper) {
            $helperSet->set($helper, $key);
        }
    }

    /**
     *
     * @param \Zend\EventManager\Event $event
     */
    public function postLoadModules(ModuleEvent $event) {

        $configListener = $event->getConfigListener();
        $listenerOptions = $configListener->getOptions();
        $serviceLocator = $event->getParam('ServiceManager');
        $config = $serviceLocator->get('config');
        $extensionsConfig = $config['sds']['doctrineExtensions'];

        if ( ! $listenerOptions->getConfigCacheEnabled() || $this->rewriteConfigCache){

            $doctrineConfig = $config['doctrine'];
            $manifest = new Manifest([
                'ExtensionConfigs' => $extensionsConfig['extensionConfigs'],
                'ServiceManagerConfig' => [
                    'aliases' => [
                        'Sds\DoctrineExtensions\DocumentManager' => $extensionsConfig['doctrine']['documentManager']
                    ]
                ]
            ]);

            //remove the rest route, if rest extension is disabled
            if (
                ! isset($extensionsConfig['extensionConfigs']['Sds\DoctrineExtensions\Rest']) ||
                ! $extensionsConfig['extensionConfigs']['Sds\DoctrineExtensions\Rest']
            ){
                unset($config['router']['routes']['rest']);
            }

            //remove the dojo_src route, if dojo extension is disabled
            if (
                ! isset($extensionsConfig['extensionConfigs']['Sds\DoctrineExtensions\Dojo']) ||
                ! $extensionsConfig['extensionConfigs']['Sds\DoctrineExtensions\Dojo']
            ){
                unset($config['router']['routes']['dojo_src']);
            }

            //Inject subscribers
            foreach ($manifest->getSubscribers() as $subscriber) {
                if ($subscriber instanceof MasterLazySubscriber){
                    $name = 'Sds\DoctrineExtensions\MasterLazySubscriber';
                    $config['service_manager']['factories'][$name] = 'Sds\DoctrineExtensionsModule\Service\MasterLazySubscriberFactory';
                    $extensionsConfig['masterLazySubscriber'] = $subscriber->getConfig();
                    $serviceLocator->setFactory($name, 'Sds\DoctrineExtensionsModule\Service\MasterLazySubscriberFactory');
                    $doctrineConfig['eventmanager'][$extensionsConfig['doctrine']['eventmanager']]['subscribers'][] = $name;
                } else {
                    $doctrineConfig['eventmanager'][$extensionsConfig['doctrine']['eventmanager']]['subscribers'][] = $subscriber;
                }
            }

            //Inject filters
            foreach ($manifest->getFilters() as $name => $class) {
                $doctrineConfig['configuration'][$extensionsConfig['doctrine']['configuration']]['filters'][$name] = $class;
            }

            //inject document paths
            $id = 0;
            foreach ($manifest->getDocuments() as $namespace => $path) {
                $name = 'sds.doctrineExtensions.'.$id;
                $doctrineConfig['driver'][$extensionsConfig['doctrine']['driver']]['drivers'][$namespace] = $name;
                $doctrineConfig['driver'][$name] = array(
                    'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
                    'paths' => array($path)
                );
                $id++;
            }

            $config['doctrine'] = $doctrineConfig;

            $extensionsConfig['serviceManagerConfig'] = $manifest->getServiceManagerConfig();

            $extensionsServiceManager = $manifest->getServiceManager();
            $config['sds']['doctrineExtensions'] = $extensionsConfig;

            $configListener->setMergedConfig($config);
            $allowOverride = $serviceLocator->getAllowOverride();
            $serviceLocator->setAllowOverride(true);
            $serviceLocator->setService('config', $config);
            $serviceLocator->setAllowOverride($allowOverride);

            if ($this->rewriteConfigCache){
                $this->writeArrayToFile($listenerOptions->getConfigCacheFile(), $config);
            }
        }

        if (!isset($extensionsServiceManager)){
            $extensionsServiceManager = ServiceManagerFactory::create(
                $extensionsConfig['serviceManagerConfig'],
                $extensionsConfig['extensionConfigs']
            );
        }
        $extensionsServiceManager->setService('documentManager', $serviceLocator->get($extensionsConfig['doctrine']['documentManager']));
        $serviceLocator->setService('Sds\DoctrineExtensions\ServiceManager', $extensionsServiceManager);

        Manifest::staticBootstrapped(
            $extensionsServiceManager
        );

        if ($serviceLocator->has('Zend\Authentication\AuthenticationService')){
            $extensionsServiceManager->setService(
                'identity',
                $serviceLocator->get('Zend\Authentication\AuthenticationService')->getIdentity()
            );
        }

    }

    protected function writeArrayToFile($filePath, $array)
    {
        $content = "<?php\nreturn " . var_export($array, 1) . ';';
        file_put_contents($filePath, $content);
        return $this;
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }
}
