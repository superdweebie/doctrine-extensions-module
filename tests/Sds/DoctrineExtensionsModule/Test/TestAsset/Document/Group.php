<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\EmbeddedDocument
 * @Sds\AccessControl({
 *     @Sds\Permission\Basic(roles="*", allow="*")
 * })
 */
class Group
{
    /** @ODM\String */
    protected $name;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function __construct($name){
        $this->name = $name;
    }
}
