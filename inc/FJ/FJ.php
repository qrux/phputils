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


use \phpseclib\Crypt\AES;



class FJ
{
    const FJ_DEFAULT_AES_MODE   = "ctr";
    const FJ_JSON_DETECT_ERRORS = false;


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
    public static function deepCopy ( $ar )
    {
        $copy = [];
        foreach ( $ar as $key => $value )
        {
            $copy[$key] = $value;
        }
        return $copy;
    }


    public static function encrypt ( $key, $iv, $plaintext )
    {
        $aes = new AES(self::FJ_DEFAULT_AES_MODE);
        $aes->setKey($key);
        $aes->setIV($iv);
        return $aes->encrypt($plaintext);
    }


    public static function decrypt ( $key, $iv, $ciphertext )
    {
        $aes = new AES(self::FJ_DEFAULT_AES_MODE);
        $aes->setKey($key);
        $aes->setIV($iv);
        return $aes->decrypt($ciphertext);
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


    public static function jsEncode ( $obj )
    {
        $json = json_encode($obj);

        if ( self::FJ_JSON_DETECT_ERRORS ) self::detectJSONError($obj, $json, true);

        return $json;
    }


    public static function jsDecode ( $json, $useAssoc = true )
    {
        $string = json_decode($json, $useAssoc);

        if ( self::FJ_JSON_DETECT_ERRORS ) self::detectJSONError($json, $string, false);

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
}
