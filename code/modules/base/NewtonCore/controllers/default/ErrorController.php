<?php

namespace NewtonCore;

use Newton\Controller\Action;

class ErrorController extends Action
{
    public function errorAction()
    {
        $this->disableLayout()->disableView();
        
        // Error output
        $errors = $this->_getParam('error_handler');
        $exception = $errors->exception;
        echo "<strong>From Core_ErrorController:<br></strong>";
        echo '<pre>', $exception->getMessage(), $exception->getTraceAsString(), '</pre>';
    }
}