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


const FJ_USE_QRCODE_LIB   = true;
const FJ_AUTOLOADER_DEBUG = false;
const FJ_DEFAULT_LIB_DIRS = "phpseclib";


/**
 * ***************1*************************************************
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
    require_once(__DIR__ . "/FJ/constants.php");
    if ( FJ_USE_QRCODE_LIB ) require_once(__DIR__ . "/FJ/lib/phpqrcode.php");

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
        require_once($path);
        return true;
    }
    else
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("File does not exist: $path");
    }

    /*
     * If we fall through, we're not using PSR-4 autoloadable classes.
     */

    if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking fall-through-1 path: $file");

    if ( file_exists($file) )
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load (fall-through-1): $file");
        require_once($file);
        return true;
    }

    //$subpath = str_replace("_", "/", $file);
    $path    = $start . "/" . $file; // $subpath;

    if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking fall-through-2 path: $path");

    if ( file_exists($path) )
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load (fall-through-2): $path");
        require_once($path);
        return true;
    }

    $subpath = str_replace("_", "/", $file);
    $path    = $start . "/" . $subpath;

    if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking fall-through-3 path: $path");

    if ( file_exists($path) )
    {
        if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load (fall-through-3): $path");
        require_once($path);
        return true;
    }

    $libDirs = FJ_DEFAULT_LIB_DIRS;
    if ( defined("LIB_DIRS") ) $libDirs .= (PATH_SEPARATOR . LIB_DIRS);

    if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking LIB_DIRS: $libDirs");

    $libsdirArray = explode(PATH_SEPARATOR, $libDirs);
    foreach ( $libsdirArray as $libdir )
    {
        $dir = $start . DIRECTORY_SEPARATOR . $libdir;

        if ( FJ_AUTOLOADER_DEBUG ) error_log("  Checking lib dir: $dir");

        if ( !is_dir($dir) ) continue;

        $path2 = $dir . DIRECTORY_SEPARATOR . $subpath;

        if ( FJ_AUTOLOADER_DEBUG ) error_log("Checking fall-through-3 path: $path2");

        if ( file_exists($path2) )
        {
            if ( FJ_AUTOLOADER_DEBUG ) error_log("Trying to load (fall-through-3): $path2");
            require_once($path2);
            return true;
        }
    }

    return false;
}


function clog ( $s1 )
{
    switch ( func_num_args() )
    {
        case 2:
            Log::log(func_get_arg(0), func_get_arg(1));
            break;

        default:
            Log::log(func_get_arg(0));
            break;
    }
}


function cclog ( $color, $mesg ) { clog(Log::color($color, $mesg)); }


function redlog ( $mesg ) { cclog(Log::TEXT_COLOR_RED, $mesg); }


function yellog ( $mesg ) { cclog(Log::TEXT_COLOR_YELLOW, $mesg); }


function grnlog ( $mesg ) { cclog(Log::TEXT_COLOR_GREEN, $mesg); }


function cynlog ( $mesg ) { cclog(Log::TEXT_COLOR_CYAN, $mesg); }


function redulog ( $mesg ) { cclog(Log::TEXT_COLOR_UL_RED, $mesg); }


function yelulog ( $mesg ) { cclog(Log::TEXT_COLOR_UL_YELLOW, $mesg); }


function grnulog ( $mesg ) { cclog(Log::TEXT_COLOR_UL_GREEN, $mesg); }


function cynulog ( $mesg ) { cclog(Log::TEXT_COLOR_UL_CYAN, $mesg); }



function looksLikeCLI () { return false == isset($_SERVER["SERVER_PORT"]); }


function isCLI () { return looksLikeCLI() && (php_sapi_name() === 'cli'); }


function isWeb () { return !isCLI(); }


// Register the autoloader.
spl_autoload_register('\FJ\autoloader');
