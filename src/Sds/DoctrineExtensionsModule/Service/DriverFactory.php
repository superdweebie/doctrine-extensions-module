<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Service;

use Doctrine\Common\Annotations;
use DoctrineModule\Options\Driver as DriverOptions;
use DoctrineModule\Service\DriverFactory as BaseDriverFactory;
use Sds\DoctrineExtensions\DummyReader;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DriverFactory extends BaseDriverFactory
{

    protected function createAnnotationReader(ServiceLocatorInterface $sl, DriverOptions $options){
        $config = $sl->get('config')['sds']['doctrineExtensions'];
        if ($config['useDummyReader']){
            $reader = new DummyReader;
        } else {
            $reader = new Annotations\AnnotationReader;
        }

        $reader = new Annotations\CachedReader(
            $reader,
            $sl->get($options->getCache())
        );
        return $reader;
    }
}
