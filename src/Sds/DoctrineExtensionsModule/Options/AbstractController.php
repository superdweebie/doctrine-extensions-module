<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Options;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\AbstractOptions;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class AbstractController extends AbstractOptions
{

    protected $serviceLocator;

    protected $documentManager;

    protected $manifestName;

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    public function getDocumentManager() {
        if (is_string($this->documentManager)) {
            $this->documentManager = $this->serviceLocator->get($this->documentManager);
        }
        return $this->documentManager;
    }

    public function setDocumentManager($documentManager) {
        $this->documentManager = $documentManager;
    }

    public function getManifestName() {
        return $this->manifestName;
    }

    public function setManifestName($manifestName) {
        $this->manifestName = (string) $manifestName;
    }
}