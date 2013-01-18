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
class JsonRestfulController extends AbstractOptions
{

    protected $serviceLocator;

    protected $serializer = 'Sds\DoctrineExtensions\Serializer';

    protected $documentValidator = 'Sds\DoctrineExtensions\DocumentValidator';

    protected $documentManager;

    protected $documentClass;

    protected $restEndpoint; //can be used instead of documentClass. The @Sds/Rest annotation will be used to look up the correct document class.

    protected $limit = '30';

    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     *
     * @param \Sds\Common\Serializer\SerializerInterface | string $serializer
     */
    public function setSerializer($serializer) {
        $this->serializer = $serializer;
    }

    public function getSerializer() {
        if (is_string($this->serializer)) {
            $this->serializer = $this->serviceLocator->get($this->serializer);
        }
        return $this->serializer;
    }

    public function getDocumentValidator() {
        if (is_string($this->documentValidator)) {
            $this->documentValidator = $this->serviceLocator->get($this->documentValidator);
        }
        return $this->documentValidator;
    }

    /**
     *
     * @param \Sds\DoctrineExtensions\Validator\DocumentValidatorInterface | string $validator
     */
    public function setDocumentValidator($documentValidator) {
        $this->documentValidator = $documentValidator;
    }

    public function getDocumentManager() {
        if (!isset($this->documentManager)){
            $this->documentManager = $this->serviceLocator->get('config')['sds']['doctrineExtensions']['doctrine']['documentManager'];
        }
        if (is_string($this->documentManager)) {
            $this->documentManager = $this->serviceLocator->get($this->documentManager);
        }
        return $this->documentManager;
    }

    public function setDocumentManager($documentManager) {
        $this->documentManager = $documentManager;
    }

    public function getDocumentClass() {

        if (! isset($this->documentClass) && isset($this->restEndpoint)){
            //attempt to get the document class by looking up the @Sds/Rest endpoint
            $documentManager = $this->getDocumentManager();
            foreach($documentManager->getMetadataFactory()->getAllMetadata() as $metadata){
                if (isset($metadata->rest) && $metadata->rest['endpoint'] == $this->restEndpoint){
                    $this->documentClass = $metadata->name;
                    break;
                }
            }
        }
        return $this->documentClass;
    }

    public function setDocumentClass($documentClass) {
        $this->documentClass = (string) $documentClass;
    }

    public function getRestEndpoint() {
        return $this->restEndpoint;
    }

    public function setRestEndpoint($restEndpoint) {
        $this->restEndpoint = (string) $restEndpoint;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function setLimit($limit) {
        $this->limit = (int) $limit;
    }
}
