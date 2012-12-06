<?php

namespace Newton\Controller;

use Zend_Controller_Action;
use Zend_Json;

class Action extends Zend_Controller_Action
{
    /*
     * Disable View
     *
     */
    public function disableView()
    {
        // Disable the view renderer
        $this->_helper->viewRenderer->setNoRender(true);

        return $this;
    }


    /*
     * Disable Layout
     *
     */
    public function disableLayout()
    {
        // Disable the Layout Renderer
        $this->_helper->layout()->disableLayout();

        return $this;
    }

    /**
     * Set layout
     * @param sting $layout Name of layout file
     */
    public function setLayout($layout)
    {
        $this->_helper->_layout->setLayout($layout);
    }

    /*
     * Send Ajax Response
     *
     * Automated ajax Response. Disables view and layout and sends
     * an ajax response depending on the type passed var.
     *
     */
    public function sendAjax($response)
    {
        $this->disableView();
        $this->disableLayout();

        if(is_string($response)) {
            echo $response;
        } elseif(is_array($response)) {
            header('Content-type: text/plain');
            echo Zend_Json::encode($response);
        }
    }
}