<?php

namespace Sds\ModuleUnitTester\BaseTest;

use Sds\DoctrineExtensionsModule\Test\TestAsset\Document\User;
use Sds\ModuleUnitTester\AbstractTest;

class SerializerTest extends AbstractTest{

    public function setUp(){
        parent::setUp();
    }

    protected function alterConfig(array $config) {
        $config['sds']['doctrineExtensions']['extensionConfigs'] = array(
            'Sds\DoctrineExtensions\Serializer' => null,
        );
        $config['doctrine']['driver']['odm_default']['drivers']['Sds\DoctrineExtensionsModule\Test\TestAsset\Document'] = 'test';
        $config['doctrine']['driver']['test'] = array(
            'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
            'paths' => array(
                __DIR__.'/TestAsset/Document'
            ),
        );

        return $config;
    }

    public function testSerializer(){
        $serializer = $this->serviceManager->get('sds.doctrineExtensions.serializer');

        $user = new User();
        $user->defineLocation('here');

        $userArray = $serializer->toArray($user);

        $this->assertEquals('here', $userArray['location']);

        $user = $serializer->fromArray($userArray, null, get_class($user));

        $this->assertEquals('here', $user->location());
    }
}
