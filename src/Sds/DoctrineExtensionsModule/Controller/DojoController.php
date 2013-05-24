<?php
/**
 * @package    Sds
 * @license    MIT
 */

namespace Sds\DoctrineExtensionsModule\Controller;

use Sds\DoctrineExtensionsModule\Exception\DocumentNotFoundException;
use Sds\DoctrineExtensionsModule\Options\DojoControllerOptions;
use Zend\Mvc\Controller\AbstractActionController;

class DojoController extends AbstractActionController
{

    protected $options;

    public function getOptions() {
        return $this->options;
    }

    public function setOptions(DojoControllerOptions $options) {
        $this->options = $options;
    }

    public function __construct(DojoControllerOptions $options = null) {
        if (!isset($options)){
            $options = new DojoControllerOptions;
        }
        $this->setOptions($options);
    }

    public function indexAction()
    {
        $module = $this->getEvent()->getRouteMatch()->getParam('module');

        $generator = $this->options->getGenerator();

        if ( ! $generator->canGenerate($module)){
            throw new DocumentNotFoundException();
        }

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent($generator->generate($module));
        return $response;
    }
}