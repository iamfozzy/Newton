<?php

namespace NewtonContent;

use NewtonCore\Model\Lang;
use NewtonCore\Model\Site;
use NewtonCore\Model\UrlRewrites;
use NewtonContent\Form;
use Newton\Config;
use Newton\Controller\Action;
use Newton\Redirect;
use Newton\URL;

class IndexController extends Action
{
    /**
     * init()
     * @return void
     */
    public function init()
    {
        // Initalizse the type manager
        Model\TypeManager::init();

        $this->view->title = 'Manage Content';
    }

    /**
     * IndexAction
     * @return [type] [description]
     */
    public function indexAction()
    {
        
    }
}