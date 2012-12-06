<?php

namespace NewtonAuth\Plugin;

use Newton;
use Newton\Config;
use NewtonCore\Model\Site;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Plugin_Abstract;
use Zend_Auth;
use Zend_Auth_Adapter_Http;
use Zend_Controller_Response_Http;
use Zend_Auth_Adapter_Http_Resolver_File;

class Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     * dispatchLoopStartup
     * 
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        // Check for authorization if authentication is enabled and the frontend name is admin
        if(Config::load('newton')->auth->enabled == true && Newton::resolve('frontendName') == 'admin') {
            $this->checkAuthorization();
        }
    }

    /**
     * Checks for authorization
     * 
     * @return [type] [description]
     */
    public function checkAuthorization()
    {
        $auth = Zend_Auth::getInstance();

        if (!$auth->hasIdentity()) {

            $config = array(
                'accept_schemes' => 'basic',
                'realm'          => 'Newton',
                'nonce_timeout'  => 3600,
            );

            $adapter = new Zend_Auth_Adapter_Http($config);
            $adapter->setRequest($this->getRequest());
            $adapter->setResponse(new Zend_Controller_Response_Http());

            $basicResolver = new Zend_Auth_Adapter_Http_Resolver_File();
            $basicResolver->setFile(APP_PATH . '/etc/users.conf');
            $adapter->setBasicResolver($basicResolver);

            $result = $auth->authenticate($adapter);
            if (!$result->isValid()) {
                $adapter->getResponse()->sendResponse();
                exit("Unauthorized.");
            }

        }
    }
}