<?php
/**
 * @link       http://superdweebie.com
 * @package    Sds
 * @license    MIT
 */
namespace Sds\DoctrineExtensionsModule;

use Traversable;
use Zend\Mvc\Router\Exception;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;

class RestRoute implements RouteInterface
{
    /**
     * RouteInterface to match.
     *
     * @var string
     */
    protected $route;

    /**
     *
     * @var type
     */
    protected $endpointToControllerMap;

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Create a new literal route.
     *
     * @param  string $route
     * @param  array  $defaults
     */
    public function __construct($route, array $endpointToControllerMap = [], array $defaults = [])
    {
        $this->route    = $route;
        $this->endpointToControllerMap = $endpointToControllerMap;
        $this->defaults = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    Route::factory()
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     * @return Literal
     */
    public static function factory($options = [])
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            throw new Exception\InvalidArgumentException('Missing "route" in options array');
        }

        if (!isset($options['endpointToControllerMap'])) {
            $options['endpointToControllerMap'] = [];
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = [];
        }

        return new static($options['route'], $options['endpointToControllerMap'], $options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    Route::match()
     * @param  Request  $request
     * @param  int|null $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = 0)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset == null) {
            $pathOffset = 0;
        }

        if (strpos($path, $this->route, $pathOffset) === $pathOffset) {
            return $this->resourceMatch(substr($path, strlen($this->route)), $request);
        }

        return null;
    }

    protected function resourceMatch($query, Request $request)
    {

        $resources = explode('/', $query);
        $index = 0;
        $params = $this->defaults;

        //parse query string
        while(true){
            if (isset($resources[$index])){
                if ($resources[$index] == ''){
                    $index++;
                    continue;
                }
                $params['restEndpoint'] = $resources[$index];
            } else {
                break;
            }
            if (isset($resources[$index + 1])){
                $params['id'] = $resources[$index + 1];
                $request->getQuery()->set($resources[$index], $resources[$index + 1]);
            } else {
                unset($params['id']);
            }
            $index += 2;
        }

        //assign controller if one is explicitly defined
        if (isset($this->endpointToControllerMap[$params['restEndpoint']])){
            $params['controller'] = $this->endpointToControllerMap[$params['restEndpoint']];
        }

        return new RouteMatch($params, strlen($query) + strlen($this->route));
    }

    /**
     * assemble(): Defined by RouteInterface interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        return $this->route;
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    Route::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return array();
    }
}
