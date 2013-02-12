<?php

namespace Sds\ModuleUnitTester\BaseTest;

use Sds\ModuleUnitTester\AbstractTest;

class ModuleTest extends AbstractTest{

    public function testModule(){
        $documentManager = $this->serviceManager->get('doctrine.documentmanager.odm_default');
        
        $metadata = $documentManager->getClassMetadata('Sds\DoctrineExtensionsModule\Test\TestAsset\Document\City');

        $this->assertInstanceOf('Sds\DoctrineExtensions\ClassMetadata', $metadata);
    }
}
