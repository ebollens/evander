<?php

/**
 * Head_Assets handles $HEAD_ASSETS loaded within the <head> tag of the template
 * file.
 *
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Head_Assets
{
    /**
     * Array that contains CSS assets to load.
     *
     * @var array
     */
    public static $_css = array();
    
    public static $_css_custom = array();
    
    /**
     * Array that contains local JS assets to load.
     *
     * @var array
     */
    public static $_js = array();

    /**
     * Array that contains external JS assets to load.
     * 
     * @var array 
     */
    public static $_js_external = array();

    public static $_js_custom = array();

    /**
     * Remove all head assets currently defined.
     */
    public static function clean()
    {
        self::$_css = array();
        self::$_css_custom = array();
        self::$_js = array();
        self::$_js_custom = array();
    }

    /**
     * Add CSS file as a new head asset.
     *
     * @param string $path
     */
    public static function css($path)
    {
        if(!in_array($path, self::$_css))
            self::$_css[] = $path;
    }
    
    public static function css_custom($code)
    {
        self::$_css_custom[] = $code;
    }

    /**
     * Add JS file as a new head asset.
     *
     * @param string $path
     */
    public static function js($path, $external = false)
    {
        if ($external === false)
        {
            if(!in_array($path, self::$_js))
                self::$_js[] = $path;
        } else {
            if (!in_array($path, self::$_js_external))
                self::$_js_external[] = $path;
        }
    }
    
    public static function js_custom($code)
    {
        self::$_js_custom[] = $code;
    }

    /**
     * Get link and script tags for the body assets.
     *
     * @return string
     */
    public static function render()
    {
        $out = '';

        foreach(self::$_css as $css)
            $out .= '<link rel="stylesheet" type="text/css" href="'.URL::css($css).'">';
        
        if(count(self::$_css_custom) > 0)
        {
            $out .= '<style>';
            foreach(self::$_css_custom as $css)
                $out .= $css . PHP_EOL;
            $out .= '</style>';
        }

        foreach(self::$_js as $js)
            $out .= '<script type="text/javascript" src="'.URL::js($js).'"></script>';
        
        if(count(self::$_js_custom) > 0)
        {
            $out .= '<script type="text/javascript">';
            foreach(self::$_js_custom as $js)
                $out .= $js . PHP_EOL;
            $out .= '</script>';
        }

        return $out;
    }
}