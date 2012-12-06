<?php

namespace NewtonCore;

use NewtonCore\Model\Setting;
use Newton\Controller\Action;
use Newton\Session;

class DashboardController extends Action
{
    public function indexAction()
    {
        $this->view->alertCount = 0;
        $this->view->alerts     = array();
    }
}