<?php
/**
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule\Test\TestAsset;

use Sds\Common\User\RoleAwareUserInterface;

class ActiveUser implements RoleAwareUserInterface
{
    protected $username = 'lucy';

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function removeRole($role) {

    }

    public function getRoles(){

    }

    public function addRole($role) {

    }

    public function setRoles(array $roles) {

    }

    public function hasRole($role) {

    }
}