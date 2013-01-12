<?php

/**
 * Body_Assets handles $BODY_ASSETS loaded into the bottom of the template file
 * right before the </body> tag.
 *
 * @package Evander
 * @author ebollens
 * @copyright Copyright (c) 2013, Eric Bollens
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0 
 */

class Body_Assets
{
    /**
     * Array that contains JS assets to load.
     *
     * @var array
     */
    public static $_js = array();

    /**
     * Remove all body assets currently defined.
     */
    public static function clean()
    {
        self::$_js = array();
    }

    /**
     * Add Javascript file as a new body asset.
     *
     * @param string $path
     */
    public static function js($path)
    {
        if(!in_array($path, self::$_js))
            self::$_js[] = $path;
    }

    /**
     * Get script tags for the body assets.
     *
     * @return string
     */
    public static function render()
    {
        $out = '';

        foreach(self::$_js as $js)
            $out .= '<script type="text/javascript" src="'.URL::js($js).'"></script>';

        return $out;
    }
}