<?php

namespace Sds\DoctrineExtensionsModule\Test\Controller;

use Sds\DoctrineExtensionsModule\Test\TestAsset\TestData;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;

class BatchJsonRestfulControllerTest extends AbstractHttpControllerTestCase{

    protected static $staticDcumentManager;

    protected static $dbDataCreated = false;

    public static function tearDownAfterClass(){
        TestData::remove(static::$staticDcumentManager);
    }

    public function setUp(){

        $this->setApplicationConfig(
            include __DIR__ . '/../../../../test.application.config.php'
        );

        parent::setUp();

        $this->documentManager = $this->getApplicationServiceLocator()->get('doctrine.documentmanager.odm_default');
        static::$staticDcumentManager = $this->documentManager;

        if ( ! static::$dbDataCreated){
            //Create data in the db to query against
            TestData::create($this->documentManager);
            static::$dbDataCreated = true;
        }
    }

    public function testBatchGet(){

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod('POST')
            ->setContent('{
                "request1": {
                    "uri": "/rest/game/feed-the-kitty",
                    "method": "GET"
                },
                "request2": {
                    "uri": "/rest/game/seven-wonders",
                    "method": "GET"
                },
                "request3": {
                    "uri": "/rest/game/does-not-extist",
                    "method": "GET"
                }
            }')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/batch');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertResponseStatusCode(200);

        $this->assertEquals(200, $result['request1']['status']);
        $this->assertEquals('no-cache', $result['request1']['headers']['Cache-Control']);
        $this->assertEquals('feed-the-kitty', $result['request1']['content']['name']);
        $this->assertEquals('dice', $result['request1']['content']['type']);

        $this->assertEquals(200, $result['request2']['status']);
        $this->assertEquals('no-cache', $result['request2']['headers']['Cache-Control']);
        $this->assertEquals('seven-wonders', $result['request2']['content']['name']);

        $this->assertEquals(404, $result['request3']['status']);
        $this->assertEquals('application/api-problem+json', $result['request3']['headers']['Content-Type']);
        $this->assertEquals('Document not found', $result['request3']['content']['title']);
    }

}
