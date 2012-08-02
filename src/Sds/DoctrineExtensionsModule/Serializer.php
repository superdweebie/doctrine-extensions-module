<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Doctrine\ODM\MongoDB\DocumentManager;
use Sds\DoctrineExtensions\Serializer\Serializer as StaticSerializer;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Serializer
{

    /**
     *
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $documentManager;

    /**
     *
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentManager() {
        return $this->documentManager;
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     */
    public function setDocumentManager(DocumentManager $documentManager) {
        $this->documentManager = $documentManager;
    }

    /**
     *
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     */
    public function __construct(DocumentManager $documentManager) {
        $this->setDocumentManager($documentManager);
    }

    /**
     *
     * @param object $document
     * @return array
     */
    public function toArray($document){
        return StaticSerializer::toArray($document, $this->documentManager);
    }

    /**
     *
     * @param array $document
     * @return string
     */
    public function toJson($document){
        return StaticSerializer::toJson($document, $this->documentManager);
    }

    /**
     *
     * @param array $data
     * @param string $classNameKey
     * @param string $className
     * @return object
     */
    public function fromArray(array $data, $classNameKey = '_className', $className = null) {
        return StaticSerializer::fromArray($data, $this->documentManager, $classNameKey, $className);
    }

    public function applySerializeMetadataToArray(array $data, $className) {
        return StaticSerializer::applySerializeMetadataToArray($data, $className, $this->documentManager);
    }
}

