<?php
/**
 * Copyright (c) 2012-2019 Troy Wu
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */


namespace FJ;


const FJ_DEFAULT_LIB_DIRS = "lib:lib/phpseclib:lib/phpqrcode";
const FJ_AUTOLOADER_DEBUG = false;



/**
 * ****************************************************************
 * SPL Autoloader.
 *
 * @param $className
 *
 * @return bool - (true) if we could find the class file and load it;
 *              (false) otherwise.
 * ****************************************************************
 */
function autoloader ( $className )
{
    $start = __DIR__;
    $dir   = dirname($start);
    if ( FJ_AUTOLOADER_DEBUG ) error_log("start: $start...");
    if ( FJ_AUTOLOADER_DEBUG ) error_log("  dir: $dir...");

    $file = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    $pwd  = getcwd();
    $path = $dir . DIRECTORY_SEPARATOR . $file;
    if ( FJ_AUTOLOADER_DEBUG ) error_log("  pwd: $pwd...");
    if ( FJ_AUTOLOADER_DEBUG ) error_log(" file: $file...");

    if ( file_exists($path) )
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load: $path");
        require $path;
        return true;
    }
    else
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("File does not exist: $path");
    }

    /*
     * If we fall through, we're not using PSR-4 autoloadable classes.
     */

    $classNameWithExtension = $className . ".php";

    if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking fall-through-1 path: $classNameWithExtension");

    if ( file_exists($classNameWithExtension) )
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load (fall-through-1): $classNameWithExtension");
        require $classNameWithExtension;
        return true;
    }

    $subpath = str_replace("_", "/", $classNameWithExtension);
    $path    = $start . "/" . $subpath;

    if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking fall-through-2 path: $path");

    if ( file_exists($path) )
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load (fall-through-2): $path");
        require $path;
        return true;
    }

    $libDirs = FJ_DEFAULT_LIB_DIRS;
    if ( defined("LIB_DIRS") ) $libDirs .= (PATH_SEPARATOR . LIB_DIRS);

    $libsdirArray = explode(PATH_SEPARATOR, $libDirs);
    foreach ( $libsdirArray as $libdir )
    {
        $dir = $start . DIRECTORY_SEPARATOR . $libdir;

        if ( !is_dir($dir) ) continue;

        $path2 = $dir . DIRECTORY_SEPARATOR . $subpath;

        if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking fall-through-3 path: $path2");

        if ( file_exists($path2) )
        {
            if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load (fall-through-3): $path2");
            require $path2;
            return true;
        }
    }

    return false;
}


function clog ( $s1, $s2 = "" ) { C::log($s1, $s2); }


function cclog ( $color, $mesg ) { clog(C::color($color, $mesg)); }


function looksLikeCLI () { return false == isset($_SERVER["SERVER_PORT"]); }


function isCLI () { return looksLikeCLI() && (php_sapi_name() === 'cli'); }


function isWeb () { return !isCLI(); }


// Include basics (like constants, etc).
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);


require_once("constants.php");
//require_once("jsconst.php");

// NOTE - Derivative constants.
//define("CLOG_SHOULD_OUTPUT_REMOTE_ADDR_INFO", (CLOG_REMOTE && isWeb()));

// Register the autoloader.
spl_autoload_register('\FJ\autoloader');


// NOTE - Load CLI MODE
// NOTE - Load CLI MODE
// NOTE - Load CLI MODE
// NOTE - Load CLI MODE
// NOTE - Load CLI MODE - If CLI mode, enter the main() method of the caller.
// NOTE - Load CLI MODE
// NOTE - Load CLI MODE
// NOTE - Load CLI MODE
// NOTE - Load CLI MODE
isCLI() && require_once('utils4cli.php');
