<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\Document
 * @Sds\Rest
 */
class City
{

    /**
     * @ODM\Id(strategy="none")
     */
    protected $name;

    /**
     * @ODM\ReferenceMany(targetDocument="State", inversedBy="cities", simple="true")
     */
    protected $states;

    /**
     * @ODM\ReferenceMany(targetDocument="Road", mappedBy="city")
     */
    protected $roads;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getStates() {
        return $this->states;
    }

    public function addState($state){
        if ( ! $this->states->contains($state)){
            $this->states->add($state);
        }
    }

    public function getRoads() {
        return $this->roads;
    }

    public function __construct($name){
        $this->name = $name;
        $this->states = new ArrayCollection;
        $this->roads = new ArrayCollection();
    }
}
