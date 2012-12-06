<?php

namespace Newton\Controller\Plugin;

use Newton;
use Newton\Config;
use Zend_Layout;
use Zend_Controller_Action_HelperBroker;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Plugin_Abstract;

class ThemeManager extends Zend_Controller_Plugin_Abstract
{
    protected $_frontendName  = 'default';
    protected $_defaultTheme  = 'default';
    protected $_defaultLayout = 'default';

    /**
     * On route shutdown, check the request depending on the frontendName
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @author Gravitywell Ltd
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $frontendName  = $request->getParam('frontendName');

        if(!empty($frontendName)) {
            $this->_frontendName = $frontendName;
        }

        // Regitser the frontend name
        $frontendName = $this->_frontendName;
        Newton::singleton('frontendName', function() use ($frontendName) {
            return $frontendName;
        });

        // Module has initied, we need to modify each modules controller directory for the correct frontend name
        Newton::resolve('front')->getDispatcher()->setFrontendName($frontendName);
    }

    /**
     * 
     * @return [type] [description]
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $frontendName  = $request->getParam('frontendName');
        $currentModule = strtolower($request->getModuleName());

        // We need to get Zend_Layout and set the layout path
        $theme  = Config::load('newton')->theme;
        $layout = Zend_Layout::getMvcInstance();
        $view   = Zend_Controller_Action_HelperBroker::getStaticHelper('Layout')->getView();

        if(empty($currentModule)) {
            $currentModule = strtolower(Config::load('newton')->modules->default);
        }

        // Set the default layout
        $layout->setLayout($this->_defaultLayout);

        $themeArray[] = $this->_defaultTheme;
        if($theme != $this->_defaultTheme) {
            $themeArray[] = $theme;
        }

        // Add the view script paths and their fallbacks
        foreach($themeArray as $themeName) {

            // Set the layout path
            $layout->getView()->addScriptPath(PUBLIC_PATH . DS . 'themes' . DS . $themeName . DS . $this->_frontendName . DS . '_layouts');

            // Set also, the view script paths...
            $view->addScriptPath(PUBLIC_PATH . DS . 'themes' . DS . $themeName . DS . $this->_frontendName . DS . '_templates' . DS . $currentModule);
        }
    }
}