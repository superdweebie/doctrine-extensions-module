<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Options;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class DojoControllerOptions extends AbstractControllerOptions
{
    protected $generator;

    public function getGenerator() {
        if (is_string($this->generator)) {
            $this->generator = $this->serviceLocator->get($this->generator);
        }
        return $this->generator;
    }

    public function setGenerator($generator) {
        $this->generator = $generator;
    }
}
