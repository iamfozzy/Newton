<?php 

namespace Newton;

use Newton;
use Zend_Log;
use Zend_log_Writer_Firebug;
use Zend_Log_Writer_Stream;

class Log
{
    /**
     * Proxy to Log::log;
     * 
     * @param  mixed $value Value to log
     * @return Zend_Log        
     */
    public static function info($value)
    {
        $priority = Zend_Log::INFO;

        return static::log($value, $priority);
    }


    /**
     * Writes to the logger, initializes if needed
     *
     * @param mixed $value Value to log
     * @return void
     */
    public static function log($value, $priority)
    {
        // Check the priority
        if(null === $priority) {
            $priority = Zend_Log::INFO;
        }

        // Check if we have a logger registered
        if(!Newton::registered('log')) {
            Newton::singleton('log', function() {
                return new Zend_Log();
            });
            
            // Add the writer
            $writer = new Zend_Log_Writer_Stream(File::storage('logs') . DS . 'info.log');
                                           
            Newton::resolve('log')->addWriter($writer);
        }
        
        Newton::resolve('log')->log($value, $priority);

        return Newton::resolve('log');
        
    } 
}