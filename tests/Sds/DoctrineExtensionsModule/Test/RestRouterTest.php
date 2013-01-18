<?php

namespace Sds\ModuleUnitTester\BaseTest;

use Sds\ModuleUnitTester\AbstractTest;
use Zend\Console\Console;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;

class RestRouterTest extends AbstractTest{

    public function setUp(){

        Console::overrideIsConsole(false);
        parent::setUp();
    }

    public function testSimpleList(){

        $request = new Request();
        $response = new Response();
        $request->setUri('http://test.com/api/state');

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('request', $request);
        $this->serviceManager->setService('response', $response);
        $this->serviceManager->setAllowOverride(false);

        $router = $this->serviceManager->get('Router');
        $routeMatch = $router->match($request);

        $this->assertNotNull($routeMatch);
        $this->assertEquals('Sds\DoctrineExtensionsModule\Controller\JsonRestfulController', $routeMatch->getParam('controller'));
        $this->assertEquals('state', $routeMatch->getParam('restEndpoint'));
    }

    public function testSimpleId(){

        $request = new Request();
        $response = new Response();
        $request->setUri('http://test.com/api/state/NSW');

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('request', $request);
        $this->serviceManager->setService('response', $response);
        $this->serviceManager->setAllowOverride(false);

        $router = $this->serviceManager->get('Router');
        $routeMatch = $router->match($request);

        $this->assertNotNull($routeMatch);
        $this->assertEquals('Sds\DoctrineExtensionsModule\Controller\JsonRestfulController', $routeMatch->getParam('controller'));
        $this->assertEquals('state', $routeMatch->getParam('restEndpoint'));
        $this->assertEquals('NSW', $routeMatch->getParam('id'));
    }

    public function testNestedList(){

        $request = new Request();
        $response = new Response();
        $request->setUri('http://test.com/api/state/NSW/city');

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('request', $request);
        $this->serviceManager->setService('response', $response);
        $this->serviceManager->setAllowOverride(false);

        $router = $this->serviceManager->get('Router');
        $routeMatch = $router->match($request);

        $this->assertNotNull($routeMatch);
        $this->assertEquals('Sds\DoctrineExtensionsModule\Controller\JsonRestfulController', $routeMatch->getParam('controller'));
        $this->assertEquals('city', $routeMatch->getParam('restEndpoint'));
        $this->assertEquals('NSW', $request->getQuery()->get('state'));
    }

    public function testNonDefaultController(){

        $request = new Request();
        $response = new Response();
        $request->setUri('http://test.com/api/state/NSW/city/Sydney/road');

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('request', $request);
        $this->serviceManager->setService('response', $response);
        $this->serviceManager->setAllowOverride(false);

        $router = $this->serviceManager->get('Router');
        $routeMatch = $router->match($request);

        $this->assertNotNull($routeMatch);
        $this->assertEquals('Sds\DoctrineExtensionsModule\Test\TestAsset\RoadController', $routeMatch->getParam('controller'));
        $this->assertEquals('road', $routeMatch->getParam('restEndpoint'));
        $this->assertEquals('Sydney', $request->getQuery()->get('city'));
        $this->assertEquals('NSW', $request->getQuery()->get('state'));
    }
}
