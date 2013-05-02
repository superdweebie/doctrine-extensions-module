<?php

namespace Sds\DoctrineExtensionsModule;

use Sds\DoctrineExtensionsModule\Test\TestAsset\Document\User;
use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;

class SerializerTest extends AbstractControllerTestCase{

    public function setUp(){

        $this->setApplicationConfig(
            include __DIR__ . '/../../../test.application.config.php'
        );

        parent::setUp();
    }

    public function testSerializer(){
        $serializer = $this->getApplicationServiceLocator()->get('Sds\DoctrineExtensions\ServiceManager')->get('serializer');

        $user = new User();
        $user->defineLocation('here');

        $userArray = $serializer->toArray($user);

        $this->assertEquals('here', $userArray['location']);

        $user = $serializer->fromArray($userArray, get_class($user));

        $this->assertEquals('here', $user->location());
    }
}
