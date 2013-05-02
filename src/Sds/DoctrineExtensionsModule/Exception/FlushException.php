<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */

namespace Sds\DoctrineExtensionsModule\Exception;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class FlushException extends \Exception implements ExceptionInterface
{
    protected $innerExceptions;

    public function getInnerExceptions() {
        return $this->innerExceptions;
    }

    public function setInnerExceptions($innerExceptions) {
        $this->innerExceptions = $innerExceptions;
    }
}
