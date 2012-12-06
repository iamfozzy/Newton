<?php

namespace Newton\Controller\Dispatcher;

use Zend_Controller_Dispatcher_Standard;
use Zend_Controller_Dispatcher_Exception;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Response_Abstract;
use Zend_Loader;


/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Dispatcher
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Standard extends Zend_Controller_Dispatcher_Standard
{
    /**
     * Format the module name.
     *
     * @param string $unformatted
     * @return string
     */
    public function formatModuleName($unformatted)
    {
        return \Newton\Module\Manager::getCorrectCaseModuleName($unformatted);
    }

    /**
     * Format action class name
     *
     * @param string $moduleName Name of the current module
     * @param string $className Name of the action class
     * @return string Formatted class name
     */
    public function formatClassName($moduleName, $className)
    {
        return '\\' . $this->formatModuleName($moduleName) . '\\' . $className;
    }

    /**
     * Returns TRUE if the Zend_Controller_Request_Abstract object can be
     * dispatched to a controller.
     *
     * Use this method wisely. By default, the dispatcher will fall back to the
     * default controller (either in the module specified or the global default)
     * if a given controller does not exist. This method returning false does
     * not necessarily indicate the dispatcher will not still dispatch the call.
     *
     * @param Zend_Controller_Request_Abstract $action
     * @return boolean
     */
    public function isDispatchable(Zend_Controller_Request_Abstract $request)
    {
        $className = $this->getControllerClass($request);

        if (!$className) {
            return false;
        }

        $finalClass  = $className;

        if (class_exists($finalClass, false)) {
            return true;
        }

        $finalClass = $this->formatClassName($this->_curModule, $className);

        $fileSpec    = $this->classToFilename($className);
        $dispatchDir = $this->getDispatchDirectory();
        $test        = $dispatchDir . DIRECTORY_SEPARATOR . $fileSpec;

        return Zend_Loader::isReadable($test);
    }


    /**
     * Load a controller class
     *
     * Attempts to load the controller class file from
     * {@link getControllerDirectory()}.  If the controller belongs to a
     * module, looks for the module prefix to the controller class.
     *
     * @param string $className
     * @return string Class name loaded
     * @throws Zend_Controller_Dispatcher_Exception if class not loaded
     */
    public function loadClass($className)
    {
        $finalClass  = $this->formatClassName($this->_curModule, $className);

        if (class_exists($finalClass, false)) {
            return $finalClass;
        }

        $dispatchDir = $this->getDispatchDirectory();
        $loadFile    = $dispatchDir . DIRECTORY_SEPARATOR . $this->classToFilename($className);

        if (Zend_Loader::isReadable($loadFile)) {
            include_once $loadFile;
        } else {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Cannot load controller class "' . $className . '" from file "' . $loadFile . "'");
        }

        if (!class_exists($finalClass, false)) {
            require_once 'Zend/Controller/Dispatcher/Exception.php';
            throw new Zend_Controller_Dispatcher_Exception('Invalid controller class ("' . $finalClass . '")');
        }

        return $finalClass;
    }


    /**
     * Sets the frontend name.
     *
     * Rotates through each module and appends the frontend name to the directory :)
     * 
     * @param string $name Frontend Name (ie 'admin', 'default', 'mobile')
     */
    public function setFrontendName($name)
    {
        foreach($this->_controllerDirectory as $k => &$v) {
            $v = $v . DS . $name;
        }
    }
}
