<?php

namespace NewtonCore;

use Newton\Request;
use Newton\Redirect;
use NewtonCore\Model\Site;
use Newton\Controller\Action;

class SiteController extends Action
{
    /**
     * Switches the language
     * 
     * @return void
     */
    public function switchAction()
    {
        $site = $this->getRequest()->getParam('site');

        Site::current($site);
        Site::saveSession();

        // Redirect to referer
        Redirect::to(Request::referrer());
    }
}