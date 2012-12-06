<?php

namespace NewtonContent\Model;

use Newton\StdObject;

class Template extends StdObject
{
    /**
     * Default options
     * 
     * @var array
     */
    protected $_defaultData = array(
        'name'                          => '',
        'label'                         => ''
    );

    /**
     * Constructor
     *
     */
    public function _construct()
    {
        // Now, we need to ensure all options are set, if not, add the default one
        foreach($this->_defaultData as $k => $v) {
            if(!isset($this->_data[$k])) {
                $this->_data[$k] = $v;
            }
        }
    }
}