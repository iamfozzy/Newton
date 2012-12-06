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
        echo "<strong>ErrorController:<br></strong>";
        echo '<pre style="padding: 10px;background-color: #eee;">', $exception->getMessage(), $exception->getTraceAsString(), '</pre>';
        echo '<pre style="padding: 10px;background-color: #eee;line-height: 1.5">'; debug_print_backtrace(); echo '</pre>';
    }
}