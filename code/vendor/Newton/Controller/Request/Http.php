<?php

namespace Newton\Controller\Request;

use Zend_Controller_Request_Http;

/**
 * Newton\Controller\Request\Http
 */
class Http extends Zend_Controller_Request_Http
{
    /**
     * Set the module name to use. 
     * Overriden to set the correct case module name every time
     *
     * @param string $value
     * @return Zend_Controller_Request_Abstract
     */
    public function setModuleName($value)
    {
        $this->_module = \Newton\Module\Manager::getCorrectCaseModuleName($value);
        return $this;
    }

     /**
     * Set a userland parameter
     *
     * Uses $key to set a userland parameter. If $key is an alias, the actual
     * key will be retrieved and used to set the parameter.
     *
     * Overriden to always set the correct case module name
     *
     * @param mixed $key
     * @param mixed $value
     * @return Zend_Controller_Request_Http
     */
    public function setParam($key, $value)
    {
        $key = (null !== ($alias = $this->getAlias($key))) ? $alias : $key;

        if('module' == $key) {
            $value = \Newton\Module\Manager::getCorrectCaseModuleName($value);
        }

        parent::setParam($key, $value);
        return $this;
    }
}
