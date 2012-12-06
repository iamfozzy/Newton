<?php 

namespace NewtonContent\Model;

use Newton\Config;
use Newton\Str;
use Newton\StdObject;
use NewtonCore\Model\Lang;
use NewtonCore\Model\Site;
use NewtonCore\Model\UrlRewrites;

use Newton\Mongo\Document;

class Item extends Document
{
    /**
     * Collection name
     * @var string
     */
    protected static $_collection = 'content';

    /**
     * Creates a new Content Item. Prepares default data
     * 
     * @param array $data   [description]
     * @param array $config [description]
     */
    public function __construct($data = array(), $config = array()) 
    {
        $langBase       = Lang::base();
        $siteCurrent    = Site::current();

        // Create the data array
        if(!isset($data['data'][$langBase])) {
            $data['data'][Lang::base()] = array();
        }

        // Store the site
        if(!isset($data['site'])) {
            $data['site'] = Site::current();
        }

        // Store the active state of this item
        if(!isset($data['published'])) {
            $data['published'] = false;
        }

        // Prepare the default data array
        parent::__construct($data, $config);
    }


    /**
     * Sets the data for this content item
     * 
     * @param array $data [description]
     * @return $this
     */
    public function setData(array $data, $lang = null)
    {
        if(null === $lang) {
            $lang = Lang::current();
        }

        if($data instanceof StdObject) {
            $data = $data->toArray();
        }

        $data = $this->sanitise($data);

        // Get the current data
        $currentData = $this->getData($lang);

        // We set like this so that other modules can still 
        // modify this items data, without fear of it being removed here
        foreach($data as $k => $v) {
            $currentData[$k] = $v;
        }

        $this->data->$lang = $currentData->toArray();

        return $this;
    }

    /**
     * Retrieves data for the current language.
     * 
     * @param  [type] $lang [description]
     * @param  [type] $site [description]
     * @return [type]       [description]
     */
    public function getData($lang = null)
    {
        if(null === $lang) {
            $lang = Lang::current();
        }

        $data = (null === $this->data->$lang) ? array() : $this->data->$lang;

        // If this is not the base language and the configuration tells us to fallback
        // get the rest of the data from the base language
        if(true == Config::load('newton')->language->fallback 
           && $lang != Lang::base()
        ){
            $dataBase = $this->data->{Lang::base()};

            if(null !== $dataBase) {
                foreach($dataBase as $k => $v) {
                if(empty($data[$k])){
                        $data[$k] = $v;
                    }
                }
            }
        }

        // If data is a Document, we want an array
        if($data instanceof \Shanty_Mongo_Document) {
            $data = $data->export();
        }

        return new StdObject($data);
    }


    /**
     * Creates url rewrite
     * 
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function updateUrlRewrite($url = null, $prefix = '', $suffix = '')
    {
        $newUrl = '';

        // Fetch the previous url rewrite
        $rewrite = $this->getRewrite();

        // Are new ones being set?
        if(null === $url && !$rewrite) {

            // We need to create our own rewrite because none is set
            $key = $this->getType()->getRewriteKey();
            $title = $this->getData()->getData($key);

            // If no title, exit because we can't do it
            if(empty($title)) {
                return false;
            }

            $url = !empty($prefix) ? $prefix . '/' : '';
            $url = $url . Str::slug($title);

            // true as the last parameter will generate a rewrite that does't exist if this does
            $url = UrlRewrites::exists($url, null, null, true);

            // Okay, we've generated a valid url.
            $newUrl = $url;

        } else {

            // If the url trying to be set already exists, we cannot use it
            if(UrlRewrites::exists($url)) {
                return false;
            }

            // New url is changed
            $newUrl = $url;
        }        

        // If none, create one
        if(!$rewrite) {
            $rewrite = UrlRewrites::createRewrite($newUrl, array(
                'module'        => 'NewtonContent',
                'controller'    => 'index',
                'action'        => 'view',
                'lang'          => Lang::current(),
                'params'        => array(
                    'id'        => (string) $this->getId()
                )
            ));
        } else {
            $rewrite->setProperty('url', $newUrl);
            $rewrite->save();
        }
    }  

    /**
     * Proxy to getRewrites
     *
     * @see getRewrites()
     * @return [type] [description]
     */
    public function getRewrite($lang = null)
    {
        // Language
        $lang = null === $lang ? Lang::current() : $lang;

        // Find the rewrites
        $rewrite = UrlRewrites::fetchOne(array(
            'params.id'     => (string) $this->getId(),
            'lang'          => $lang,
            'site'          => $this->getProperty('site')
        ));

        return $rewrite;
    }


    /**
     * Returns all rewrites
     * @return [type] [description]
     */
    public function getRewrites()
    {
        // Find the rewrites
        $rewrites = UrlRewrites::all(array(
            'params.id'     => (string) $this->getId(),
            'site'          => $this->getProperty('site')
        ));

        return $rewrites;
    }


    /**
     * Before an item is deleted, this is ran.
     * @return [type] [description]
     */
    public function preDelete()
    {
        // We want to clean all rewrites from this document
        $this->removeUrlRewrite();
    }



    /**
     * Removes oldRewrites from this item
     * 
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function removeUrlRewrite()
    {
        // Flush for this document
        $rewrites = $this->getRewrites();

        foreach($rewrites as $rewrite) {
            $rewrite->delete();
        }
    }

    /**
     * Sanitises data to remove things we don't need
     * 
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function sanitise($data = null)
    {
        if(null === $data) {
            $data = &$this->_data;
        }

        unset($data['Save']);
        unset($data['submit']);
        unset($data['MAX_FILE_SIZE']);

        return $data;
    }


    /**
     * Retrieves the type for this content item
     * 
     * @return [type] [description]
     */
    public function getType()
    {
        return TypeManager::getTypeByName($this->type);
    }

    /**
     * Finds content items based type. Then sorts, limits and skips if needed
     * @return [type] [description]
     */
    public static function findContent($type = null, $sort = null, $limit = null, $skip = null)
    {
        // Does it have any linked content defined?
        $filter = array(
            'site'      => Site::current()
        );

        // Apply type filter if specified
        if(null !== $type) {
            $filter['type'] = $type;
        }

        // Get the cursor
        $cursor = static::all($filter);

        // Check for a sort, and modify it for the current language
        if($sort !== null) {
            $newSort = array();
            foreach($sort as $k => $v) {
                $newSort['data.' . Lang::current() . '.' . $k] = $v;
            }

            $cursor->sort($newSort);
        }

        // Apply limit
        if($limit !== null) {
            $cursor->limit($limit);
        }

        // Apply the skip (offset)
        if($skip !== null) {
            $cursor->skip($skip);
        }

        // Return the result
        return $cursor;
    }
}