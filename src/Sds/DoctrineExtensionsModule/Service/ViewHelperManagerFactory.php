<?php

namespace Sds\DoctrineExtensionsModule\Service;

use Zend\Mvc\Service\ViewHelperManagerFactory as BaseViewHelperManagerFactory;

class ViewHelperManagerFactory extends BaseViewHelperManagerFactory
{
    /**
     * An array of helper configuration classes to ensure are on the helper_map stack.
     *
     * @var array
     */
    protected $defaultHelperMapClasses = array(
    );
}
