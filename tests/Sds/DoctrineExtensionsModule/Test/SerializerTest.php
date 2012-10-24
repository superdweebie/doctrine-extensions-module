<?php

namespace Sds\ModuleUnitTester\BaseTest;

use Sds\DoctrineExtensionsModule\Test\TestAsset\Document\User;
use Sds\ModuleUnitTester\AbstractTest;

class SerializerTest extends AbstractTest{

    public function testSerializer(){
        $serializer = $this->serviceManager->get('Sds\DoctrineExtensions\Serializer');

        $user = new User();
        $user->defineLocation('here');

        $userArray = $serializer->toArray($user);

        $this->assertEquals('here', $userArray['location']);

        $user = $serializer->fromArray($userArray, null, get_class($user));

        $this->assertEquals('here', $user->location());
    }
}
