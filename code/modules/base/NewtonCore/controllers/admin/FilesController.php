<?php

namespace NewtonCore;

use Newton\Controller\Action;

class FilesController extends Action
{
    /**
     * Show the filebrowser
     * 
     * @return [type] [description]
     */
    public function indexAction()
    {
        $this->view->title = 'Manage Files';
    }

    /**
     * Shows the filemanager embedded
     * 
     * @return [type] [description]
     */
    public function embedAction()
    {
        // Change the layout to the embedded elfinder one
        $this->_helper->layout->setLayout('filemanager');
        $this->disableView();
    }
}