<?php 

namespace NewtonCore\Model;

use Newton\Config;
use Newton\Mongo\Document;

class UrlRewrites extends Document
{
    protected static $_collection = 'url_rewrites';

    /**
     * Creates a url to content relation
     * 
     * @param  [type] $url    [description]
     * @param  [type] $data   [description]
     * @param  [type] $params [description]
     * @param  bool   $appendRandom Whether to append a random number to the end
     * @return [type]         [description]
     */
    public static function createRewrite($url, $data)
    {
        // No url? Return false
        if(empty($url)) {
            return false;
        }

        $data = array(
            'url'           => $url,
            'module'        => $data['module'],
            'controller'    => $data['controller'],
            'action'        => $data['action'],
            'lang'          => isset($data['lang']) ? $data['lang'] : Lang::current(),
            'site'          => isset($data['site']) ? $data['site'] : Site::current(),
            'params'        => $data['params']
        );

        // Check if the url has been made?
        $rewrite = self::fetchOne(array(
            'url'   => $url,
            'lang'  => Lang::current(),
            'site'  => Site::current()
        ));

        // Found a match?...
        if(null !== $rewrite) {
            return false;
        }

        $rewrite = new self();

        foreach($data as $k => $v) {
            $rewrite->setProperty($k, $v);
        }

        $rewrite->save();

        return $rewrite;
    }


    /**
     * Find a match by Url
     * @param  [type] $url [description]
     * @return [type]      [description]
     */
    public static function findByUrl($url, $lang = null, $site = null)
    {
        if(null === $lang) {
            $lang = Lang::current();
        }

        if(null === $site) {
            $site = Site::current();
        }

        // Attempt to find a match by Url for this site and language
        $result = self::fetchOne(array(
            'url' => $url,
            'site'  => $site,
            'lang'  => $lang
        ));

        // If no result, attempt to find for this site and base language
        if(
            empty($result)
            && Lang::base() != $lang 
            && true == Config::load('newton')->language->fallback
        ) {
            $result = self::fetchOne(array(
                'url'   => $url,
                'site'  => $site,
                'lang'  => Lang::base()
            ));
        }

        return $result;
    }

    /**
     * Checks if the URL Exists
     * @param  [type]  $url [description]
     * @return boolean      [description]
     */
    public static function exists($url, $lang = null, $site = null, $generateNew = false)
    {
        if(null === $lang) {
            $lang = Lang::current();
        }

        if(null === $site) {
            $site = Site::current();
        }

        // Attempt to find a match by Url for this site and language
        $result = self::fetchOne(array(
            'url' => $url,
            'site'  => $site,
            'lang'  => $lang
        ));

        // Attempt to generate one that does
        if($generateNew) {
            $i       = 0;
            $exists  = true;
            while(true === $exists) {

                $url   .= ($i++ > 0) ? static::getRandomSuffix() : '';
                $result = self::fetchOne(array(
                    'url' => $url,
                    'site'  => $site,
                    'lang'  => $lang
                 ));

                $exists = empty($result) ? false : true;
            }

            return $url;
        }

        if($result) {
            return true;
        }

        return false;
    }


    /**
     * Match a url by its params and language
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public static function findByParams($params, $lang = null)
    {
        if(null === $lang) {
            $lang = Lang::current();
        }

        // Go...
        return self::fetchOne(array(
            'lang'      => $lang,
            'params'    => $params
        ));
    }


    /**
     * Generates a random suffic for the url
     * @return [type] [description]
     */
    public static function getRandomSuffix()
    {
        return '-' . substr(dechex(mt_rand()), 0, 4);
    }
}