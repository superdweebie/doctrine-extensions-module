<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\Document
 */
class Game {

    /**
     * @ODM\Id(strategy="NONE")
     */
    protected $name;

    /**
     * @ODM\String
     * @Sds\RequiredValidator
     */
    protected $type;

    public function __construct($name, $type) {
        $this->name = $name;
        $this->type = $type;
    }

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
}
