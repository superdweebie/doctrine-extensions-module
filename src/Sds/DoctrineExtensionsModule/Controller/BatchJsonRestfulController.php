<?php
/**
 * @package    Sds
 * @license    MIT
 */

namespace Sds\DoctrineExtensionsModule\Controller;

use Sds\DoctrineExtensions\Exception;
use Sds\DoctrineExtensionsModule\Options\BatchJsonRestfulController as Options;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

class BatchJsonRestfulController extends AbstractRestfulController
{

    protected $options;

    public function getOptions() {
        return $this->options;
    }

    public function setOptions(Options $options) {
        $this->options = $options;
    }

    public function __construct(Options $options = null) {
        if (!isset($options)){
            $options = new Options;
        }
        $this->setOptions($options);
    }

    public function create($data) {
        $router = $this->serviceLocator->get('router');
        $controllerLoader = $this->serviceLocator->get('controllerLoader');

        foreach ($data as $key => $requestData){
            $request = new Request();
            $request->setMethod($requestData['method']);
            $request->setUri($requestData['uri']);
            if (isset($requestData['headers'])){
                foreach ($requestData['headers'] as $name => $value){
                    $request->getHeaders()->addHeader($name, $value);
                }
            }
            $request->getHeaders()->addHeader($this->request->getHeaders()->get('Accept'));
            if (isset($requestData['content'])){
                $request->setContent = $requestData['content'];
            }

            $match = $router->match($request);
            if ($match->getMatchedRouteName() != 'rest'){
                //shouldn't throw - need to return serialzied exception
                throw new Exception\RuntimeException(sprintf(
                    '%s uri is not a rest route, so is not supported by batch controller.', $requestData['uri']
                ));
            }
            $event = new MvcEvent;
            $event->setRouteMatch($match);
            $response = new Response;

            $controller = $controllerLoader->get($match->getParam('controller'));
            $controller->setEvent($event);

            try {
                $contentModel = $controller->dispatch($request, $response);
            } catch (\Exception $exception) {
                $event->setError(Application::ERROR_EXCEPTION);
                $event->setParam('exception', $exception);
                $this->options->getExceptionViewModelPreparer()->prepareExceptionViewModel($event);
                $contentModel = $event->getResult();
            }

            $headers = [];
            foreach ($response->getHeaders() as $header){
                $headers[$header->getFieldName()] = $header->getFieldValue();
            }
            $responseModel = new JsonModel([
                'status' => $response->getStatusCode(),
                'headers' => $headers
            ]);
            $responseModel->addChild($contentModel, 'content');
            $this->model->addChild($responseModel, $key);
        }

        return $this->model;
    }

    public function onDispatch(MvcEvent $e) {
        $this->model = $this->acceptableViewModelSelector($this->options->getAcceptCriteria());
        return parent::onDispatch($e);
    }

    public function getList(){
        throw new Exception\RuntimeException(sprintf(
            '%s is not supported', __METHOD__
        ));
    }

    public function get($id){
        throw new Exception\RuntimeException(sprintf(
            '%s is not supported', __METHOD__
        ));
    }

    public function delete($id) {
        throw new Exception\RuntimeException(sprintf(
            '%s is not supported', __METHOD__
        ));
    }

    public function deleteList() {
        throw new Exception\RuntimeException(sprintf(
            '%s is not supported', __METHOD__
        ));
    }

    public function update($id, $data) {
        throw new Exception\RuntimeException(sprintf(
            '%s is not supported', __METHOD__
        ));
    }
}