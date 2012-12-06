<?php

namespace Newton\Controller\Plugin;

use Newton;
use Newton\Config;
use NewtonCore\Model\Site;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Plugin_Abstract;

class AdminManager extends Zend_Controller_Plugin_Abstract
{
    /**
     * dispatchLoopStartup
     * 
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        // Initialize the site manager if this is an admin route base on sessions
        if(Newton::resolve('frontendName') == 'admin') {

            // Tell the site manager to detect the current site based on a session variable
            Site::detectSession();
        }
    }
}