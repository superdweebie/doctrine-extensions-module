<?php

namespace Sds\DoctrineExtensionsModule\Test;

use Sds\DoctrineExtensionsModule\Test\TestAsset\Document\City;
use Sds\DoctrineExtensionsModule\Test\TestAsset\Document\Road;
use Sds\DoctrineExtensionsModule\Test\TestAsset\Document\State;
use Sds\ModuleUnitTester\AbstractTest;
use Zend\Console\Console;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;

class RestRouteControllerIntegrationTest extends AbstractTest{

    public function setUp(){

        Console::overrideIsConsole(false);
        parent::setUp();
    }

    public function testGetNestedList(){

        $documentManager = $this->serviceManager->get('doctrine.documentmanager.odm_default');

        $nsw = new State('NSW');
        $documentManager->persist($nsw);
        $vic = new State('Vic');
        $documentManager->persist($vic);

        $city = new City('Sydney');
        $city->addState($nsw);
        $documentManager->persist($city);

        $road = new Road('Lucy St');
        $road->setCity($city);
        $documentManager->persist($road);

        $road = new Road('Toby Av');
        $road->setCity($city);
        $documentManager->persist($road);

        $road = new Road('Miriam Rd');
        $road->setCity($city);
        $documentManager->persist($road);

        $city = new City('Wollongong');
        $city->addState($nsw);
        $documentManager->persist($city);

        $road = new Road('Other Rd');
        $road->setCity($city);
        $documentManager->persist($road);

        $city = new City('Nowra');
        $city->addState($nsw);
        $documentManager->persist($city);

        $city = new City('Springwood');
        $city->addState($nsw);
        $documentManager->persist($city);

        $city = new City('Melbourne');
        $city->addState($vic);
        $documentManager->persist($city);

        $city = new City('Geelong');
        $city->addState($vic);
        $documentManager->persist($city);

        $city = new City('Ballarat');
        $city->addState($vic);
        $documentManager->persist($city);

        $city = new City('Nhill');
        $city->addState($vic);
        $documentManager->persist($city);

        $documentManager->flush();
        $documentManager->clear();


        $request = new Request();

        $response = new Response();
        $request->setUri('http://test.com/api/state/NSW/city/Wollongong/road');

        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('request', $request);
        $this->serviceManager->setService('response', $response);
        $this->serviceManager->setAllowOverride(false);

        $router = $this->serviceManager->get('Router');
        $routeMatch = $router->match($request);

        $this->application->getMvcEvent()->setRouteMatch($routeMatch);

        $controller = $this->serviceManager->get('ControllerLoader')->get($routeMatch->getParam('controller'));
        $controller->setEvent($this->application->getMvcEvent());

        $result = $controller->dispatch($request, $response);
        $returnArray = $result->getVariables();

        $this->assertCount(1, $returnArray);
        $this->assertEquals('Other Rd', $returnArray[0]['name']);
    }
}
