<?php

namespace NewtonAuth;

use Newton;
use Newton\Event;
use Newton\Controller\Action;
use Newton\Module\ModuleInterface;

/**
 * NewtonAuth Module
 *
 * Implements the most basic of authentication for the time being
 *
 * TODO: Make this proper!
 */
class Module implements ModuleInterface
{
    /**
     * init
     * 
     * @return void
     */
    public static function init()
    {
        // Register the front controller plugin
        Event::listen('front.init', function() {
            Newton::resolve('front')->registerPlugin(new Plugin\Auth());
        });
    }
}