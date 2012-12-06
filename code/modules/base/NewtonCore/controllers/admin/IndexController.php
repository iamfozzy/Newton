<?php

namespace NewtonCore;

use Newton\Redirect;
use Newton\URL;
use Newton\Controller\Action;

class IndexController extends Action
{
    public function indexAction()
    {
        // Redirect to the Dashboard
        Redirect::to(URL::route(array(
            'controller' => 'dashboard'
        )));
    }
}