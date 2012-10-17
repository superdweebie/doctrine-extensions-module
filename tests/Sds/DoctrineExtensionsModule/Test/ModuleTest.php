<?php

namespace Sds\ModuleUnitTester\BaseTest;

use Sds\DoctrineExtensionsModule\Test\TestAsset\User;
use Sds\ModuleUnitTester\AbstractTest;

class ModuleTest extends AbstractTest{

    public function testModule(){
        $documentManager = $this->serviceManager->get('doctrine.documentmanager.odm_default');
    }
}
