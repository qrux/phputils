<?php
/**
 * Copyright (c) 2012 Troy Wu
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/*
 * For instance, if including-file (e.g., index.php) includes this file (utils.php),
 * and is found in .. and this file is in ../inc/utils.php, then usage is like this:
 */
// require_once(__DIR__.'/'/*DON'T EDIT BEFORE THIS*/ . "../inc/utils.php");

date_default_timezone_set("UTC");

function looksLikeCLI ()
{
    return false == isset($_SERVER["SERVER_PORT"]);
}

function isCLI ()
{
    return looksLikeCLI() && (php_sapi_name() === 'cli');
}

function str2hex ( $string ) { return bin2hex($string); }

function hex2str ( $hex ) { return (string)pack("H*", $hex); }

function color ( $color, $str ) { return $color . $str . TEXT_COLOR_SUFFIX; }

function todate () { return date('Ymd'); }

function totime () { return date('Ymd_his'); }

function clog ()
{
    $prefix = FJ::clogMakePrefix();
    $argc   = func_num_args();

    if ( 2 == $argc )
    {
        $desc       = func_get_arg(0);
        $item       = func_get_arg(1);
        $descString = color(TEXT_COLOR_CYAN, $desc . ": ");
        $longPrefix = $prefix . $descString;
    }
    else
    {
        $item       = func_get_arg(0);
        $desc       = "";
        $longPrefix = $prefix;
    }

    if ( is_scalar($item) )
        FJ::clogHandleScalar($longPrefix, $item);
    else
        FJ::clogHandleObject($prefix, $desc, $item);
}

function cclog ( $color, $mesg ) { clog(color($color, $mesg)); }


class FJ
{
    public static function boolToClogString ( $boolVal ) { return $boolVal ? color(TEXT_COLOR_GREEN, "TRUE") : color(TEXT_COLOR_RED, "FALSE"); }


    public static function clogHandleScalar ( $prefix, $scalar )
    {
        if ( is_bool($scalar) )
            $mesg = FJ::boolToClogString($scalar);
        else
            $mesg = color(TEXT_COLOR_YELLOW, strval($scalar));

        error_log($prefix . $mesg);
    }


    public static function generateUniqueID ( $data )
    {
        $ver    = 1;
        $md5    = md5($data);
        $revmd5 = md5(strrev($data));
        $sha512 = hash("sha512", $data);

        $guid = $ver . "-" . $md5 . "-" . $revmd5 . "-" . $sha512;
        return $guid;
    }


    /**
     * ****************************************************************
     * Pretty-prints a dump of the current call-stack.
     * ****************************************************************
     */
    public static function dump ()
    {
        switch ( func_num_args() )
        {
            case 2:
                clog(func_get_arg(0), func_get_arg(1));
                break;

            default:
                clog(func_get_arg(0));
                break;
        }

        try
        {
            throw new Exception();
        }
        catch ( Exception $e )
        {
            clog($e);
        }
    }

    public static function warn ()
    {
        $argc = func_num_args();

        if ( 2 == $argc )
        {
            $desc = func_get_arg(0);
            $item = func_get_arg(1);
            clog($desc, color(TEXT_COLOR_UL_RED, $item));
        }
        else
        {
            $item = func_get_arg(0);
            clog(color(TEXT_COLOR_UL_RED, $item));
        }
    }


    public static function error ( $mesg )
    {
        if ( FJ_REPORT_ERROR_VERBOSE )
        {
            try
            {
                throw new Exception($mesg);
            }
            catch ( Exception $e )
            {
                clog($e);
                if ( FJ_ABORT_ON_ERROR ) exit();
            }
        }
        clog(color(TEXT_COLOR_UL_RED, $mesg));
        return false;
    }

    /**
     * https://stackoverflow.com/questions/2791998/convert-dashes-to-camelcase-in-php
     *
     * @param      $string
     * @param bool $startWithLower
     *
     * @return mixed|string
     */
    public static function dashesToCamelCase ( $string, $startWithLower = true )
    {
        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
        return $startWithLower ? lcfirst($str) : $str;
    }

    /**
     * @param      $string
     *
     * @return mixed|string
     */
    public static function dotsToDashes ( $string )
    {
        return str_replace('.', '_', $string);
    }

    /**
     * ****************************************************************
     * Takes an input value, and returns its truthiness. as a string.
     *
     * @param $val - Value to check for truthiness.
     *
     * @return string - ("true") or ("false")
     * ****************************************************************
     */
    public static function bs ( $val ) { return ($val ? "true" : "FALSE"); }


    /**
     * ****************************************************************
     * Creates a 2nd-level copy of an array.  The references are copied,
     * but the values themselves are unchanged.
     *
     * @param $ar
     *
     * @return array
     * ****************************************************************
     */
    public static function copyArray ( $ar )
    {
        $copy = array();
        foreach ( $ar as $key => $value )
        {
            $copy[$key] = $value;
        }
        return $copy;
    }


    /**
     * ****************************************************************
     * Gets the URL of the current page.
     *
     * @return string
     * ****************************************************************
     */
    public static function currentPageURL ()
    {
        if ( isCLI() ) return "";

        $pageURL = 'http';
        if ( isset($_SERVER["HTTPS"]) && (strncasecmp($_SERVER["HTTPS"], "OFF", 3)) )
        {
            $pageURL .= "s";
        }

        $pageURL .= "://";

        if ( $_SERVER["SERVER_PORT"] != "80" )
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        }
        else
        {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }


    /**
     * ****************************************************************
     * Gets the data from a URL.
     *
     * @param string $url - Input URL
     *
     * @return string - Output URL
     * ****************************************************************
     */
    public static function getTinyURL ( $url )
    {
        $urlToMakeTiny = 'http://tinyurl.com/api-create.php?url=' . $url;
        return FJ::getURLContents($urlToMakeTiny);
    }


    public static function convertPasswordToStars ( $password )
    {
        $starCount = strlen($password);
        $stars     = "";
        for ( $i = 0; $i < $starCount; ++$i )
        {
            $stars .= "*";
        }

        return $stars;
    }


    public static function getURLContents ( $url )
    {
        $ch      = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    public static function makePost ( $url, $params )
    {
        //
        // A very simple PHP example that sends a HTTP POST to a remote site
        //
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "postvar1=value1&postvar2=value2&postvar3=value3");

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        // in real life you should use something like:
        // curl_setopt($ch, CURLOPT_POSTFIELDS,
        //          http_build_query(array('postvar1' => 'value1')));

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);

        clog("FJ.makePost() - server-output", $server_output);
        // further processing ....
        if ( $server_output == "OK" )
        {
            clog("FJ.makePost()", "WIN");
        }
        else
        {
            clog("FJ.makePost()", "FAIL");
        }
    }


    public static function clogMakePrefix ()
    {
        $time = $remote = "";

        if ( CLOG_TIMING )
        {
            $time = microtime(true);
            $time = $time - floor($time);
            $time = sprintf("%0.3f", $time);
            $time .= ' ';
            $time = color(TEXT_COLOR_RED, $time);
        }

        if ( CLOG_SHOULD_OUTPUT_REMOTE_ADDR_INFO )
        {
            $remote = $_SERVER['REMOTE_ADDR'] . ":" . $_SERVER['REMOTE_PORT'] . " ";
            $remote = color(TEXT_COLOR_YELLOW, $remote);
        }

        $prefix = $time . $remote;

        return $prefix;
    }


    public static function clogHandleObject ( $prefix, $desc, $item )
    {
        if ( null === $item )
        {
            $str = color(TEXT_COLOR_BG_RED, "[NULL object]");
            $str = "== " . color(TEXT_COLOR_YELLOW, $desc) . " " . $str . " ==";
            error_log($prefix . $str);
            return;
        }
        else if ( is_array($item) )
        {
            $descString = (0 == strlen($desc)) ? "<Array>" : "$desc <Array>";

            FJ::clogHandleArray($prefix, $descString, $item);
        }
        else if ( $item instanceof Exception )
        {
            FJ::clogHandleException($item);
        }
        else
        {
            if ( $item instanceof Wired )
            {
                $ref        = new ReflectionClass($item);
                $type       = $ref->getName();
                $descString = (0 == strlen($desc)) ? "<$type>" : "$desc <$type>";
                $wiredHash  = call_user_func(array($item, "toHash"));

                FJ::clogHandleArray($prefix, $descString, $wiredHash);
                return;
            }
            else
            {
                try
                {
                    $ref   = new ReflectionClass($item);
                    $type  = $ref->getName();
                    $data  = var_export($item, true);
                    $color = TEXT_COLOR_RED;

                    $str = FJ::json_encode($data);

                    $type = color(TEXT_COLOR_YELLOW, "<$type>");
                    $str  = color($color, $str);

                    error_log($prefix . "$type: $str");
                }
                catch ( ReflectionException $e )
                {
                    clog($e);
                }
            }
        }
    }


    /**
     * ****************************************************************
     * Pretty-prints an array object.
     *
     * Handles recursively defined arrays.
     *
     * @param string $prefix
     * @param string $desc - Description to be printed above array
     *                     contents.
     * @param array  $item
     * @param int    $depth
     * ****************************************************************
     */
    public static function clogHandleArray ( $prefix, $desc, $item, $depth = 0 )
    {
        $indent = self::clogCreateIndent($depth);

        //error_log("clogHandleArray/prefix: $prefix");
        //error_log("clogHandleArray/indent: [$indent]");
        //error_log("clogHandleArray/parent-pre: [$parentPre]");
        //print_r($item);

        $count = count($item);

        if ( 0 == $count )
        {
            $str = color(TEXT_COLOR_BG_RED, "[EMPTY array]");
            $str = "== " . color(TEXT_COLOR_YELLOW, $desc) . " " . $str . " ==";
            error_log($prefix . $str);
            return;
        }

        $arKeys = array_keys($item);
        if ( is_int($arKeys[0]) )
        {
            $padding   = ceil(log10($count));
            $preFormat = "  [%{$padding}d]: ";
            $blank     = "%-{$padding}s  ";
            $keyColor  = TEXT_COLOR_WHITE;
        }
        else if ( CLOG_ARRAY_KEY_FANCY && is_string($arKeys[0]) )
        {
            $padding = 0;
            foreach ( $arKeys as $k )
            {
                $len = strlen($k);
                if ( $len > $padding )
                {
                    $padding = $len;
                }
            }
            $preFormat = "  [%{$padding}s]: ";
            $blank     = "%-{$padding}s  ";
            $keyColor  = TEXT_COLOR_CYAN;
        }
        else
        {
            $preFormat = "  [%s]: ";
            $blank     = "%s";
            $keyColor  = TEXT_COLOR_CYAN;
        }

        $pre = sprintf($blank, $desc);
        $pre = color(TEXT_COLOR_UL_YELLOW, $pre);

        //error_log("clogHandleArray/pre: $pre");

        if ( 0 === $depth )
        {
            error_log($prefix . $pre);
        }
        else
        {
            //error_log($prefix . $parentPre . "<Array>");
        }

        //if ( 0 !== $depth )
        //$prefix = self::clogCreatePlaceholder(strlen($prefix), ' ');

        foreach ( $item as $key => $val )
        {
            $pre = sprintf($preFormat, $key);
            $pre = color($keyColor, $pre);

            if ( is_array($val) )
            {
                $post = color(TEXT_COLOR_UL_CYAN, "Array");
                $str  = $pre . $post;
                error_log($prefix . $indent . $str);

                // Recursion.
                self::clogHandleArray($prefix, $desc, $val, 1 + $depth, $pre);
            }
            else
            {
                $val = self::clogHandlePasswords($key, $val);

                $post = color(TEXT_COLOR_GREEN, $val);
                $str  = $pre . $post;

                error_log($prefix . $indent . $str);
            }
        }
    }


    private static function clogCreateIndent ( $depth )
    {
        $indentCount = (CLOG_DEPTH_INDENT * $depth) + ((0 === $depth) ? 0 : 2);
        return self::clogCreatePlaceholder($indentCount);
    }


    private static function clogCreatePlaceholder ( $len, $char = ' ' )
    {
        $str = "";
        while ( $len-- )
        {
            $str .= $char;
        }
        return $str;
    }


    private static function clogHandlePasswords ( $key, $val )
    {
        // Deal with password-like fields.

        return !preg_match(CLOG_PASSWORD_PATTERN, $key) ? $val : self::clogCreatePlaceholder(strlen($val), "*");
    }


    /**
     * ****************************************************************
     * Pretty-prints and Exception object.
     *
     * @param Exception $ex
     * ****************************************************************
     */
    public static function clogHandleException ( $ex )
    {
        if ( $ex instanceof FJEX && $ex->getCode() < 0 )
        {
            $str = sprintf("========######## [ %s ] ########========", $ex->getMessage());
            $str = color(TEXT_COLOR_UL_YELLOW, $str);
            error_log($str);
            return;
        }

        $depth = 0;

        $prefixLen = strlen(dirname(__DIR__)) + 1;
        $file      = $ex->getFile();
        $file      = substr($file, $prefixLen);
        $mesg      = $ex->getMessage();

        $str = sprintf("%3d) %s - (%s:%d)", $depth, $mesg, $file, $ex->getLine());
        $str = color(TEXT_COLOR_BG_RED, $str);
        error_log($str);

        $trace      = $ex->getTrace();
        $traceCount = count($trace);

        for ( $i = ($traceCount - 1); $i >= 0; --$i )
        {
            ++$depth;
            $exceptionLineGap = CLOG_MESG_EXCEPTION_WIDTH;

            $frame  = array_shift($trace);
            $file   = isset($frame['file']) ? $frame['file'] : "?";
            $line   = isset($frame['line']) ? $frame['line'] : "?";
            $caller = isset($frame['function']) ? $frame['function'] : "?";
            if ( isset($frame['class']) )
            {
                $class  = $frame['class'];
                $caller = "$class.$caller";
            }

            $file = basename($file);
            $mesg = "$file:$line";

            $str = sprintf("%3d) %s%-{$exceptionLineGap}s - (%s)", $depth, "", $caller, $mesg);
            $str = color(TEXT_COLOR_BG_RED, $str);
            error_log($str);
        }
    }


    /**
     * ****************************************************************
     * Base64url-encodes the input.
     *
     * @param $s string - Input (plain)
     *
     * @return string - base64url-encoding of input.
     * ****************************************************************
     */
    public static function b64url_encode ( $s )
    {
        if ( false === isset($s) || null === $s || 0 == strlen($s) )
        {
            return null;
        }

        /*
        * Do stuff!
        */
        $b64 = base64_encode($s);
        //$b64u = rtrim( strtr( $b64, '+/', '-_' ), '=' );
        $b64u = strtr($b64, '+/', '-_');

        return $b64u;
    }


    /**
     * ****************************************************************
     * Base64url-decodes the input.
     *
     * @param $s string - Input (base64url-encoded).
     *
     * @return string - base64url-decoding of input.
     * ****************************************************************
     */
    public static function b64url_decode ( $s )
    {
        if ( false === isset($s) || null === $s || 0 == strlen($s) )
        {
            return null;
        }

        //return base64_decode( str_pad( strtr( $s, '-_', '+/' ), strlen( $s ) % 4, '=', STR_PAD_RIGHT ) );
        return base64_decode(strtr($s, '-_', '+/'));
    }

    public static function randBytes ( $length )
    {
        // method 1. the fastest
        if ( function_exists('openssl_random_pseudo_bytes') )
        {
            return openssl_random_pseudo_bytes($length);
        }
        // method 2
        static $fp = true;
        if ( $fp === true )
        {
            // warning's will be output unles the error suppression operator is used. errors such as
            // "open_basedir restriction in effect", "Permission denied", "No such file or directory", etc.
            $fp = @fopen('/dev/urandom', 'rb');
        }
        if ( $fp !== true && $fp !== false )
        { // surprisingly faster than !is_bool() or is_resource()
            return fread($fp, $length);
        }
        // method 3. pretty much does the same thing as method 2 per the following url:
        // https://github.com/php/php-src/blob/7014a0eb6d1611151a286c0ff4f2238f92c120d6/ext/mcrypt/mcrypt.c#L1391
        // surprisingly slower than method 2. maybe that's because mcrypt_create_iv does a bunch of error checking that we're
        // not doing. regardless, this'll only be called if this PHP script couldn't open /dev/urandom due to open_basedir
        // restrictions or some such
        if ( function_exists('mcrypt_create_iv') )
        {
            return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        }

        // We've failed to get a good random number; throw exception.
        throw new Exception("Could not get high-quality random number (openssl, /dev/urandom, mcrypt all FAILED); aborting.");
    }

    /**
     * ****************************************************************
     * SPL Autoloader.
     *
     * @param $className
     * ****************************************************************
     */
    public static function autoloader ( $className )
    {
        $classNameWithExtension = $className . ".php";

        if ( file_exists($classNameWithExtension) )
        {
            require_once($classNameWithExtension);
            return;
        }

        $start   = __DIR__;
        $subpath = str_replace("_", "/", $classNameWithExtension);
        $path    = $start . "/" . $subpath;

        if ( file_exists($path) )
        {
            require_once($path);
            return;
        }

        $libsdirArray = explode(PATH_SEPARATOR, LIB_DIRS);
        foreach ( $libsdirArray as $libdir )
        {
            $dir   = $start . DIRECTORY_SEPARATOR . $libdir;
            $path2 = $dir . DIRECTORY_SEPARATOR . $subpath;

            if ( is_file($path2) )
            {
                require_once($path2);
                return;
            }
        }

        clog("Cannot find class [ $className ] in [ $path:$path2 ]; giving up");
    }


    public static function json_encode ( $obj )
    {
        $json = json_encode($obj);

        self::detectJSONError($obj, $json, true);

        return $json;
    }


    public static function json_decode ( $json, $useAssoc = true )
    {
        $string = json_decode($json, $useAssoc);

        self::detectJSONError($json, $string, false);

        return $string;
    }


    private static function detectJSONError ( $input, $output, $isEncode = true )
    {
        $function = $isEncode ? "encoding" : "decoding";

        if ( false === $output )
            clog("JSON error $function [ $input ]");

        switch ( json_last_error() )
        {
            case JSON_ERROR_NONE:
                $jsonErrorMessage = null;
                break;
            case JSON_ERROR_DEPTH:
                $jsonErrorMessage = '- Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $jsonErrorMessage = '- Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $jsonErrorMessage = '- Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $jsonErrorMessage = '- Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $jsonErrorMessage = '- Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $jsonErrorMessage = '- Unknown error';
                break;
        }

        if ( null !== $jsonErrorMessage )
            clog("-----=====[ JSON encoding error $jsonErrorMessage ]=====-----");
    }

    public static function encrypt ( $key, $iv, $plaintext )
    {
        $aes = new Crypt_AES(CRYPT_AES_MODE_CTR);
        $aes->setKey($key);
        $aes->setIV($iv);
        return $aes->encrypt($plaintext);
    }

    public static function decrypt ( $key, $iv, $ciphertext )
    {
        $aes = new Crypt_AES(CRYPT_AES_MODE_CTR);
        $aes->setKey($key);
        $aes->setIV($iv);
        return $aes->decrypt($ciphertext);
    }
}

/*
if ( false == isset($_SERVER["SERVER_PORT"]) )
{
    define("MODE_IS_CLI", true);
}
else
{
    define("MODE_IS_CLI", false);
}
*/

// Include basics (like constants, etc).
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);
$libsdirArray = explode(PATH_SEPARATOR, LIB_DIRS);
foreach ( $libsdirArray as $libdir )
{
    set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/$libdir");
}

require_once("constants.php");
//require_once("jsconst.php");

// NOTE - Derivative constants.
define("CLOG_SHOULD_OUTPUT_REMOTE_ADDR_INFO", (CLOG_REMOTE && !isCli()));

// Register the autoloader.
spl_autoload_register('FJ::autoloader');

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
