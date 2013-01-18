<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\Document
 * @Sds\Rest
 */
class State
{
    /**
     * @ODM\Id(strategy="none")
     */
    protected $name;

    /**
     * @ODM\ReferenceMany(targetDocument="City", mappedBy="states")
     */
    protected $cities;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getCities() {
        return $this->cities;
    }

    public function __construct($name){
        $this->name = $name;
        $this->cities = new ArrayCollection();
    }
}
