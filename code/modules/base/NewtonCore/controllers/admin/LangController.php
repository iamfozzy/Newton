<?php

namespace NewtonCore;

use Newton\Request;
use Newton\Redirect;
use NewtonCore\Model\Lang;
use Newton\Controller\Action;

class LangController extends Action
{
    /**
     * Switches the language
     * 
     * @return void
     */
    public function switchAction()
    {
        $lang = $this->getRequest()->getParam('lang');

        Lang::current($lang);
        Lang::saveSession();

        // Redirect to referer
        Redirect::to(Request::referrer());
    }
}