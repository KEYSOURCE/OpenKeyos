<?php
/**
 * Created by IntelliJ IDEA.
 * User: victor
 * Date: 1/10/14
 * Time: 12:43 PM
 * To change this template use File | Settings | File Templates.
 */

class Route {
    /**
     * URL of this route
     * @var string
     */
    private $url;

    /**
     * Accepted methods
     * @var array
     */
    private $methods = array('GET', 'POST', 'PUT', 'DELETE');

    /**
     * Route's target
     * @var mixed
     */
    private $target;

    /**
     * @var route name - used for reversed routing
     */
    private $name;

    /**
     * Customer parameter filters
     * @var array
     */
    private $filters = array();

    /**
     * Array containing parameters passed through request URL
     * @var array
     */
    private $parameters = array();

    public function getUrl(){
        return $this->url;
    }
    public function setUrl($url){
        $this->url = (string)$url;
        if(substr($url,-1) !== '/') $this->url .= '/';
        $this->url = $url;
    }

    public function getTarget(){
        return $this->target;
    }
    public function setTarget($target){
        $this->target = $target;
    }

    public function getFilters(){
        return $this->filters;
    }

    public function getMethods(){
        return $this->methods;
    }
    public function setMethods(array $methods){
        $this->methods = $methods;
    }

    public function getName(){
        return $this->name;
    }
    public function setName($name){
        $this->name = (string) $name;
    }

    public function setFilters(array $filters){
        $this->filters = $filters;
    }

    public function getParameters(){
        return $this->parameters;
    }
    public function setParameters(array $parameters){
        $this->parameters = $parameters;
    }

    public function getRegex() {
        return preg_replace_callback("/:(\w+)/", array(&$this, 'substituteFilter'), $this->url);
    }

    private function substituteFilter($matches) {
        if (isset($matches[1]) && isset($this->filters[$matches[1]])) {
            return $this->filters[$matches[1]];
        }

        return "([\w-]+)";
    }
}