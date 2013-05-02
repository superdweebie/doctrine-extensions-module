<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\EmbeddedDocument
 * @Sds\Permission\Basic(roles="all", allow="all")
 */
class Component {

    /**
     * @ODM\Id(strategy="NONE")
     */
    protected $name;

    /**
     * @ODM\String
     * @Sds\Validator\Required
     */
    protected $type;

    /**
     * @ODM\EmbedMany(targetDocument="Manufacturer")
     */
    protected $manufacturers;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getManufacturers() {
        return $this->manufacturers;
    }

    public function setManufacturers($manufacturers) {
        $this->manufacturers = $manufacturers;
    }

    public function __construct() {
        $this->manufacturers = new ArrayCollection();
    }
}
