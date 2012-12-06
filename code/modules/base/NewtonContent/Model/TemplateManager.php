<?php

namespace NewtonContent\Model;

use Newton\Config;
use Newton\Event;

class TemplateManager
{
    /**
     * Holds the collection of templates
     * 
     * @var array
     */
    protected $templates = array();

    /**
     * Holds the instance
     * @var [template]
     */
    protected static $instance = null;

    /**
     * Initializes the TemplateManager
     * 
     * @return [template] [despcription]
     */
    public static function init()
    {
        if(null === static::$instance) {
            static::$instance = new static();
            Event::fire('content.templates.init', array(static::$instance));
        }
    }

    /**
     * Retrieves the instance of the TemplateManager
     * @return [template] [description]
     */
    public static function getInstance()
    {
        if(null === static::$instance) {
            static::init();
        }

        return static::$instance;
    }

    /**
     * Private constructor to prevent multiple instances
     */
    private function __construct()
    {}

    /**
     * Adds a template
     * @param array $data
     * @return $this
     */
    public function addTemplate(Template $template)
    {
        $this->templates[$template->getName()] = $template;
    }

    /**
     * Retrives the templates
     * 
     * @return [template] [description]
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Retrives a template by name
     * @param  [template] $name [description]
     * @return [template]       [description]
     */
    public static function getByName($name)
    {
        foreach(static::getInstance()->getTemplates() as $template) {
            if($name == $template['name']) {
                return $template;
            }
        }
        
        throw new \Exception ("Template '$name' doesn't exist in the TemplateManager.");
    }


    /**
     * Retrieves a label for a template name
     * 
     * @param  [template] $nam [description]
     * @return [template]      [description]
     */
    public static function getLabel($name)
    {
        $template = static::getInstance()->getByName($name);
        return $template->getLabel();
    }


    /**
     * Automatically finds all templates from the theme and 
     * default theme and attempts to read and load them
     * 
     * @return void
     */
    public static function findTemplates()
    {
        $filters    = array();
        $templates  = array();
        $instance   = static::getInstance();
        $filters[]  = PUBLIC_PATH . '/themes/default/default/_templates/newtoncontent/page-templates/*.phtml';

        // Check for a theme other than default
        if(Config::load('newton')->theme != 'default') {
            $filters[] = PUBLIC_PATH . '/themes/' . Config::load('newton')->theme . '/default/_templates/newtoncontent/page-templates/.phtml';
        }
        
        // Iterate throught the glob filters, then through the files inside
        foreach($filters as $filter) {
            $files = glob($filter, GLOB_MARK);

            foreach($files as $file) {
                $basename = basename($file);
                $instance->addTemplate(new Template(array(
                    'name'      => $basename,
                    'label'     => $basename
                )));
            }
        }

    }
}