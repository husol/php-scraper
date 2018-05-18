<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of index
 *
 * @author ddminh
 */

//Set include path:
$fixedPath =  realpath(dirname(__FILE__));
$arrIncludeDir = array('class', 'config', 'library');
set_include_path(get_include_path() . implode('',  array_map(function($folder) use($fixedPath){return PATH_SEPARATOR."$fixedPath/$folder";}, $arrIncludeDir)));

function __autoload($class_name) {
	$found = stream_resolve_include_path($class_name . '.php');
    if ($found !== FALSE) {
        require_once $found;
		 return true;
    }
	die("$class_name does not exist");
	return false;
}

$scraper = new Scraper();
$scraper->scraperIt();
