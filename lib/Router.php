<?php
/**
 * Created by IntelliJ IDEA.
 * User: victor
 * Date: 1/10/14
 * Time: 1:53 PM
 * To change this template use File | Settings | File Templates.
 */
require_once('Route.php');

class Router {
    private $routes = array();
    private $namedRoutes = array();
    private static $instance = NULL;
    /**
     * Base REQUEST_URI - gets prepend to all route's url
     * @var string
     */
    private $basePath = '';

    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Router();
        }
        return self::$instance;
    }


    function setBasePath($basePath){
        $this->basePath = $basePath;
    }

    /**
     * Route factory method
     * Map the given URL to the given target
     *
     */
    public function map($routeUrl, $target='', array $args=array()){
        $route = new Route();
        $route->setUrl($this->basePath . $routeUrl);
        $route->setTarget($target);

        if(isset($args['methods'])){
            if(is_array($$args['methods'])){
                $route->setMethods($args['methods']);
            }
        }

        if(isset($args['name'])){
            $route->setName($args['name']);
            if(!isset($this->namedRoutes[$route->getName()])) $this->namedRoutes[$route->getName()] = $route;
        }

        if(isset($args['filters'])){
            $route->setFilters($args['filters']);
        }

        $this->routes[] = $route;
    }

    /**
     * Match request against mapped routes
     */
    public function matchCurrentRequest(){
        $requestMethod = (isset($_POST['_method']) && ($_method = strtoupper($_POST['_method'])) && in_array($_method, array('PUT', 'DELETE'))) ? $_method : $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REQUEST_URI'];

        //strip GET vars from url
        if(($pos = strpos($requestUrl, '?')) !== false){
            $requestUrl = substr($requestUrl, 0, $pos);
        }

        return $this->match($this->basePath . $requestUrl, $requestMethod);
    }

    /**
     * match a given url and method and see if a route was defined for it
     * if so call the route's target
     * @param $requestUrl
     * @param $requestMethod
     */
    public function match($requestUrl, $requestMethod){
        foreach($this->routes as $route){
            if(!in_array($requestMethod, $route->getMethods())) continue;

            //check if the requestUrl matches the route regex, if not return false
            //debug($route->getRegex());
            //debug($requestUrl);
            if(!preg_match("@^" . $route->getRegex() . "$@i", $requestUrl, $matches)) continue;

            $params = array();

            if(preg_match_all("/:([\w-]+)/", $route->getUrl(), $argument_keys)){
                $argument_keys = $argument_keys[1];

                foreach($argument_keys as $key => $name){
                    if(isset($matches[$key + 1])) $params[$name] = $matches[$key + 1];
                }
            }

            $route->setParameters($params);
            return $route;
        }
    }

    public function get_named_route($route_name){
        if(isset($this->namedRoutes[$route_name])) return $this->namedRoutes[$route_name];
        return false;
    }

    /**
     * Reverse route a named route
     * @param $routeName  - The name of the route to reverse route
     * @param array $params - Optional array of paramters to use in URL
     * @return string The url to the route
     */
    public function generate($routeName, array $params=array()){

        // Check if route exists
        if (!isset($this->namedRoutes[$routeName]))
            throw new Exception("No route with the name $routeName has been found.");


        $route = $this->namedRoutes[$routeName];
        $url = $route->getUrl();

        // replace route url with given parameters
        if ($params && preg_match_all("/:(\w+)/", $url, $param_keys)) {

            // grab array with matches
            $param_keys = $param_keys[1];

            // loop trough parameter names, store matching value in $params array
            foreach ($param_keys as $i => $key) {
                if (isset($params[$key]))
                    $url = preg_replace("/:(\w+)/", $params[$key], $url, 1);
            }
        }

        return $url;
    }
}