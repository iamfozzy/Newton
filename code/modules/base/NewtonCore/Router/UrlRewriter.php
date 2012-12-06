<?php

namespace NewtonCore\Router;

use NewtonCore\Model\UrlRewrites as UrlRewriteModel;
use NewtonCore\Model\Lang;
use Zend_Config;
use Zend_Controller_Router_Route_Abstract as AbstractRouter;

class UrlRewriter extends AbstractRouter
{
    protected $_route = null;
    protected $_defaults = array(
        'module'        => 'content',
        'controller'    => 'index',
        'action'        => 'index'
    );
    protected $_params = array();

    /**
     * Return 1 so we get path info sent to match and not the request object
     *
     */
    public function getVersion() {
        return 1;
    }

    /**
     * Instantiates route based on passed Zend_Config structure
     *
     * @param Zend_Config $config Configuration object
     */
    public static function getInstance(Zend_Config $config)
    {
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        return new self($config->route, $defs);
    }

    /**
     * Prepares the route for mapping.
     *
     * @param string $route Map used to match with later submitted URL path
     * @param array $defaults Defaults for map variables with keys as variable names
     */
    public function __construct($route, $defaults = array())
    {
        $this->_route = $route;
        $this->_defaults = array_merge($this->_defaults, (array) $defaults);
    }


    /**
     * Matches a user submitted path with a previously defined route.
     * Assigns and returns an array of defaults on a successful match.
     *
     * @param string $path Path used to match against this routing map
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path, $partial = false)
    {        
        $path = trim($path, '/');

        // If path is empty default to home
        if(empty($path) || 'root' == $path) {
            $path = 'home';
        }

        // Attempt to find a match
        $match = UrlRewriteModel::findByUrl($path);

        if(null === $match) {   
            return false;
        }

        $this->_params['module']        = $match['module'];
        $this->_params['controller']    = $match['controller'];
        $this->_params['action']        = $match['action'];
        $this->_params['params']        = is_object($match['params']) ? $match['params']->export() : $match['params'];

        $params = array_merge($this->_defaults, $this->_params);

        return $params;     
    }



    /**
     * Assembles a URL path defined by its parameters
     *
     * @param int|string Content Id
     * @return string Route path with user submitted parameters
     */
    public function assemble($params = array(), $reset = false, $encode = false)
    {
        $rewrite = UrlRewritesModel::findByParams($params);

        return (string) $rewrite->url;
    }


    /**
     * Return a single parameter of route's defaults
     *
     * @param string $name Array key of the parameter
     * @return string Previously set default
     */
    public function getDefault($name) {
        if (isset($this->_defaults[$name])) {
            return $this->_defaults[$name];
        }
        return null;
    }

    /**
     * Return an array of defaults
     *
     * @return array Route defaults
     */
    public function getDefaults() {
        return $this->_defaults;
    }



}
