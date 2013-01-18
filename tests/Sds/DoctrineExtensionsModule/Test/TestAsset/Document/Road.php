<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\Document
 * @Sds\Rest
 */
class Road
{
    /**
     * @ODM\Id(strategy="none")
     */
    protected $name;

    /**
     * @ODM\ReferenceOne(targetDocument="City", inversedBy="roads", simple="true")
     */
    protected $city;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getCity() {
        return $this->city;
    }

    public function setCity($city){
        $this->city = $city;
    }

    public function __construct($name){
        $this->name = $name;
    }
}
