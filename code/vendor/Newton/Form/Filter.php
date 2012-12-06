<?php

namespace Newton\Form;

class Filter
{
    /**
     * Sanitise form input data
     * 
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function sanitiseForm($data)
    {
        // unset non content form elements
        unset($data['Save']);
        unset($data['MAX_FILE_SIZE']);

        return $data;
    }
}