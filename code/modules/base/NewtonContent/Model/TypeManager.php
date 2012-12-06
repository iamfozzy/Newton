<?php

namespace NewtonContent\Model;

use Newton\Form;
use Newton\Event;

class TypeManager
{
    /**
     * Holds the collection of ContentTypes
     * 
     * @var array
     */
    protected $types = array();

    /**
     * Holds the instance
     * @var [type]
     */
    protected static $instance = null;

    /**
     * Initializes the TypeManager
     * 
     * @return [type] [despcription]
     */
    public static function init()
    {
        if(null === static::$instance) {
            static::$instance = new static();
            Event::fire('content.types.init', array(static::$instance));
        }
    }

    /**
     * Retrieves the instance of the TypeManager
     * @return [type] [description]
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
     * Adds a type
     * @param array $data
     * @return $this
     */
    public function addType(Type $type)
    {
        $this->types[$type->getName()] = $type;
    }

    /**
     * Retrives the types
     * 
     * @return [type] [description]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Retrives a type by name
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function getTypeByName($name)
    {
        foreach(static::getInstance()->getTypes() as $type) {
            if($name == $type['name']) {
                return $type;
            }
        }
        
        throw new \Exception ("Type '$name' doesn't exist in the TypeManager.");
    }


    /**
     * Retrives a form for a type name
     * 
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public static function getForm($name)
    {
        $type = static::getInstance()->getTypeByName($name);
        return $type->getForm();
    }       

    /**
     * Retrieves a label for a type name
     * 
     * @param  [type] $nam [description]
     * @return [type]      [description]
     */
    public static function getLabel($name)
    {
        $type = static::getInstance()->getTypeByName($name);
        return $type->getLabel();
    }
}