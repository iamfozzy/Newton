<?php

namespace Newton;

use Zend_Cache_Manager;

class Cache extends Zend_Cache_Manager
{
    protected static $_instance = null;

    public static function factory($template = 'default')
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance->getCache($template);
    }

    /**
     * Initialize some templates
     */
    public function __construct()
    {
        $this->_optionTemplates['default'] = array(
            'frontend' => array(
                'name'    => 'Core',
                'options' => array(
                    'automatic_serialization' => true,
                ),
            ),
            'backend' => array(
                'name'    => 'File',
                'options' => array(
                    'cache_dir' => File::storage('cache')
                ),
            ),
        );

        // Config cacher - works on filetimes
        $this->_optionTemplates['config'] = array(
            'frontend' => array(
                'name'    => 'File',
                'options' => array(
                    'master_files'  => array(
                        BP . DS . 'etc' . DS . 'local.yaml',
                        BP . DS . 'etc' . DS . 'newton.yaml',
                        BP . DS . 'etc' . DS . 'alias.yaml'
                    ),
                    'automatic_serialization' => true,
                ),
            ),
            'backend' => array(
                'name'    => 'File',
                'options' => array(
                    'cache_dir' => File::storage('cache')
                ),
            ),
        );
    }
}