<?php

namespace Newton\Mongo;

use Newton\Config;
use NewtonCore\Model\Site;
use Shanty_Mongo_Document;

class Document extends Shanty_Mongo_Document
{
    /**
     * Gets the Database Name
     * 
     * @return string Name of the database
     */
    public static function getDbName()
    {
        return Config::load('newton')->dbname;
    }

    /**
     * Sanitise form input data
     * 
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function sanitise($data = null)
    {
        if(null === $data) {
            $data = &$this->_data;
        }

        // unset non content form elements
        $data['submit'] = null;
        $data['Save'] = null;
        $data['MAX_FILE_SIZE'] = null;

        return $data;
    }
}