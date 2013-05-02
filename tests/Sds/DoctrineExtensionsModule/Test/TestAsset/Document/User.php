<?php

namespace Sds\DoctrineExtensionsModule\Test\TestAsset\Document;

use Doctrine\Common\Collections\ArrayCollection;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Sds\DoctrineExtensions\Annotation\Annotations as Sds;

/**
 * @ODM\Document
 * @Sds\Rest
 * @Sds\Permission\Basic(roles="all", allow="all")
 */
class User {

    /**
     * @ODM\Id(strategy="NONE")
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     * @Sds\Serializer\Ignore
     */
    protected $password;


    /** @ODM\EmbedMany(targetDocument="Group") */
    protected $groups;

    /** @ODM\EmbedOne(targetDocument="Profile") */
    protected $profile;

    /**
     * @ODM\Field(type="string")
     */
    protected $location;

    public function __construct($id = null)
    {
        $this->id = $id;
        $this->groups = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function location() {
        return $this->location;
    }

    public function defineLocation($location) {
        $this->location = $location;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups(array $groups){
        $this->groups = $groups;
    }

    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    public function getProfile() {
        return $this->profile;
    }

    public function setProfile(Profile $profile) {
        $this->profile = $profile;
    }
}
