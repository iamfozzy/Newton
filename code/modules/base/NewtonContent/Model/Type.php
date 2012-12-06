<?php

namespace NewtonContent\Model;

use Newton\StdObject;
use NewtonCore\Model\Site;

class Type extends StdObject
{
    /**
     * Default options
     * 
     * @var array
     */
    protected $_defaultData = array(
        'name'                          => '',
        'label'                         => '',
        'plural'                        => null,
        'form'                          => null,
        'url_rewrites_enabled'          => true,
        'auto_create_url_rewrite'       => true,
        'rewrite_key'                   => 'title',
        'is_page'                       => true,        // Pages are viewed all as the same type
        'url_suffix'                    => '',
        'url_prefix'                    => '',
        'is_visible_in_sitemap'         => true,
        'is_visible_in_content_list'    => true,
        'template'                      => 'default',   // This is the template that renders this type by default. This can be changed.
        'datatable_mappings'            => array(
            'title'     => 'Title',
            'url'       => 'URL'
        )
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

    /**
     * Override getForm because form might be a closure
     * 
     * @return [type] [description]
     */
    public function getForm()
    {
        $form = isset($this->_data['form']) ? $this->_data['form'] : null;

        if(is_callable($form)) {
            return $form();
        } else {
            return $form;
        }
    }

    /**
     * Returns the plural
     * @return [type] [description]
     */
    public function getPluralLabel()
    {
        if(null === $this->_data['plural']) {
            return \Inflector::pluralize($this->_data['label']);
        } elseif (false === $this->_data['plural']) {
            return $this->_data['label'];
        } else {
            return $this->_data['plural'];
        }
    }
}