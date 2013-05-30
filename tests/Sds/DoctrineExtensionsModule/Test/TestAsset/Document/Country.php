<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\Document
 * @Sds\AccessControl({
 *     @Sds\Permission\Basic(roles="*", allow="*")
 * })
 */
class Country {

    /**
     * @ODM\Id(strategy="NONE")
     */
    protected $name;

    /**
     * @ODM\ReferenceMany(targetDocument="Author", mappedBy="country")
     */
    protected $authors;

    public function __construct() {
        $this->authors = new ArrayCollection();
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getAuthors() {
        return $this->authors;
    }

    public function setAuthors($authors) {
        $this->authors = $authors;
    }
}
