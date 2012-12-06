<?php

namespace Newton;

use Zend_Session;
use Zend_Session_Namespace as SessionNamespace;

class Session extends Zend_Session
{

    /**
     * Have the messages been sent?
     * @var boolean
     */
    protected static $flashMessagesSent = false;

    /**
     * Holds the flash messages
     * 
     * @var array
     */
    protected static $flashMessages = array();


    /**
     * Starts the session
     *
     * Sets the default options for newton
     * 
     * @param  boolean $options [description]
     * @return [type]           [description]
     */
    public static function start($options = false)
    {
        $config = Config::load('newton')->session;

        $newtonOptions = array(
            'save_path'             => File::storage('sessions'),
            'name'                  => $config->name,
            'remember_me_seconds'   => (int) $config->remember_me_seconds,
            'strict'                => false
        );

        if ($options) {
            $newtonOptions = array_merge($newtonOptions, $options);
        }

        // Restore flash messages from the last session
        static::restoreFlashMessages();

        // Register the shutdown function to store the messages in the session for next opening
        register_shutdown_function('\Newton\Session::saveFlashMessages');

        parent::start($newtonOptions);
    }


    /**
     * Flashes a message to the user
     * 
     * @param  [type] $message [description]
     * @param  string $status  [description]
     * @return [type]          [description]
     */
    public static function flashMessage($message, $status = 'info')
    {
        static::$flashMessages[] = array(
            'message'   => $message,
            'status'    => $status
        );
    }

    /**
     * Gets all the flash messages
     * 
     * @return [type] [description]
     */
    public static function getFlashMessages()
    {
        $output = '';

        foreach(static::$flashMessages as $message) {
            $output .= '<div class="alert alert-' . $message['status'] . '">' . $message['message'] . '</div>';
        }

        static::$flashMessagesSent = true;

        return $output;
    }
    

    /**
     * Saves any flash messages not shown into the session
     * 
     * @return [type] [description]
     */
    public static function saveFlashMessages()
    {
        if (!empty(static::$flashMessages) && false === static::$flashMessagesSent) {
            $session = new SessionNamespace('flash-messenger');
            $session->messages = static::$flashMessages;
        }
    }

    /**
     * Restores the flash messages from the session
     * 
     * @return [type] [description]
     */
    public static function restoreFlashMessages()
    {
        $session  = new SessionNamespace('flash-messenger');
        $messages = $session->messages;

        if(!empty($messages)) {
            static::$flashMessages = $messages;
            unset($session->messages);
        }
    }
}