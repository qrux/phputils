<?php
/**
 * Copyright (c) 2012-2020 Troy Wu
 * Copyright (c) 2021      Version2 OÜ
 * All rights reserved.
 *
 * SHOULD THE COPYRIGHT HOLDERS GRANT PERMISSION TO USE THIS SOFTWARE,
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



use Exception;
use \phpseclib\Crypt\AES;



class FJ
{
    const FJ_DEFAULT_AES_MODE   = "ctr";
    const FJ_JSON_DETECT_ERRORS = false;



    public static function totime () { return date("Ymd_his"); }
    public static function todate () { return date("Ymd"); }



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



    public static function stripNon7BitCleanASCII ( $string )
    {
        return preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $string);
    }



    public static function stripSpaces ( $string )
    {
        return preg_replace("/[^[:alnum:]]/", "", $string);
    }


    public static function stripLower ( $string )
    {
        return strtolower(self::stripSpaces($string));
    }


    public static function spacesToDashes ( $string )
    {
        return preg_replace("/[[:space:]]/", "-", $string);
    }



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



    public static function hash ( $str, $substrLen = 0, $algo = "SHA256" )
    {
        $hash = hash($algo, $str);
        return (0 < $substrLen) ? substr($hash, 0, $substrLen) : $hash;
    }



    public static function hashFile ( $path, $substrLen = 0, $algo = "SHA256" )
    {
        $hash = hash_file($algo, $path);
        return (0 < $substrLen) ? substr($hash, 0, $substrLen) : $hash;
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



    public static function in_array_sorted ( $needle, $haystack )
    {
        $top = count($haystack) - 1;
        $bot = 0;
        while ( $top >= $bot )
        {
            $p = ($top + $bot) >> 1;
            if ( $haystack[$p] < $needle ) $bot = $p + 1;
            elseif ( $haystack[$p] > $needle ) $top = $p - 1;
            else return true;
        }
        return false;
    }



    public static function endsWith ( $needle, $haystack, $len = false )
    {
        if ( false === $len ) $len = strlen($needle);
        if ( 0 == $len ) return true;

        return $needle === substr($haystack, -$len);
    }



    public static function startsWith ( $needle, $haystack, $len = false )
    {
        if ( false === $len ) $len = strlen($needle);
        return $needle === substr($haystack, 0, $len);
    }



    public static function stripExtension ( $filename )
    {
        $extWithPeriod = strrchr($filename, ".");
        $extlen        = strlen($extWithPeriod);
        $hasPeriod     = 0 < $extlen;
        $namelen       = strlen($filename) - $extlen;
        $name          = $hasPeriod ? substr($filename, 0, $namelen) : $filename;

        return $name;
    }



    public static function stripExtensionAndDir ( $filepath )
    {
        $basename = basename($filepath);
        return self::stripExtension($basename);
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
     * Takes a string of the form 'abcxyz' and converts it to 'ab...yz'.
     *
     * @param string $string    - Input string.
     * @param int    $len       - Total length to clip to (including delimiter).
     * @param string $delimiter - Combining string to split front- and back-halves.
     *
     * @return string - Either a clipped string 'ab...yz' or the original string.
     */
    public static function clipString ( $string, $len, $delimiter = " )->( " )
    {
        $l = strlen($string);
        if ( $l < $len ) return $string;

//        clog("clipping!");

        $dlen = strlen($delimiter);

        $slen      = $len - $dlen;
        $half      = $slen / 2;
        $fh        = floor($half);
        $otherHalf = 0 === ($half - $fh) ? $fh : ($fh + 1);
        $end       = $l - $otherHalf;

//        clog("dlen", $dlen);
//        clog("slen", $slen);
//        clog("half", $half);
//        clog("fh", $fh);
//        clog("o-ha", $otherHalf);
//        clog("dlen", $dlen);
//        clog("end", $end);

        $front = substr($string, 0, $half);
        $back  = substr($string, $end);

//        clog("front", $front);
//        clog("back", $back);

        return $front . $delimiter . $back;
    }



    public static function jsEncode ( $obj )
    {
        $json = json_encode($obj);

        if ( self::FJ_JSON_DETECT_ERRORS ) self::detectJSONError($obj, $json, true);

        return $json;
    }



    public static function jsPrettyEncode ( $obj )
    {
        $json = json_encode($obj, JSON_PRETTY_PRINT);

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



    /**
     * @param $length
     *
     * @return bool|string
     * @throws Exception
     */
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
     * @param string     $method
     * @param string     $url
     * @param bool|array $data
     * @param bool|array $params
     *
     * @return string - raw (textual) HTTP response
     */
    public static function callAPI ( $method, $url, $data = false, $params = false )
    {
        $debug        = self::getParam("debug", $params);
        $debugVerbose = self::getParam("debugVerbose", $params);
        $user         = self::getParam("user", $params);
        $pass         = self::getParam("pass", $params);
        $debugProxy   = self::getParam("debugProxy", $params);
        $debugPort    = self::getParam("debugPort", $params);
        $debugHost    = self::getParam("debugHost", $params);

        $curl = curl_init();

        switch ( $method )
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ( false !== $data )
                {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    if ( false !== $debugVerbose ) clog("POST data (as query string)", http_build_query($data));
                }
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;

            default:
                if ( false !== $data )
                {
                    if ( false !== $debugVerbose ) clog("GET data (query string)", http_build_query($data));
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        if ( false !== $pass && false !== $user )
        {
            // Optional Authentication:
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, "$user:$pass");
        }

        if ( false !== $debug ) clog("Hitting URL", $url);
        if ( false !== $debugVerbose ) curl_setopt($curl, CURLOPT_VERBOSE, true);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if ( false !== $debugProxy )
        {
            //
            // Defaults set to Charles Debugging, locally.
            //
            $port = false === $debugPort ? 8888 : $debugPort;
            $host = false === $debugHost ? "localhost" : $debugHost;

            curl_setopt($curl, CURLOPT_PROXY, "http://$host:$port/");
        }

        $result = curl_exec($curl);

        curl_close($curl);

        return trim($result);
    }



    static function getParam ( $needle, $haystack )
    {
        if ( false === $haystack || !$haystack ) return false;
        return array_key_exists($needle, $haystack) ? $haystack[$needle] : false;
    }



    /**
     * Method: POST, PUT, GET etc
     * Data: array("param" => "value") ==> index.php?param=value
     *
     * @param      $method
     * @param      $url
     * @param bool $data
     * @param bool $debug - Only use if CharlesProxy is being used on localhost:8888
     *
     * @return string
     */
    public static function _callAPI ( $method, $url, $data = false, $debug = false )
    {
        $curl = curl_init();

        switch ( $method )
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ( false !== $data )
                {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    if ( false !== $debug ) clog("POST data (as query string)", http_build_query($data));
                }
                break;

            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;

            default:
                if ( false !== $data )
                {
                    if ( false !== $debug ) clog("GET data (query string)", http_build_query($data));
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

//        // Optional Authentication:
//        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//        curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        if ( false !== $debug ) clog("Hitting URL", $url);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if ( false !== $debug )
        {
            curl_setopt($curl, CURLOPT_PROXY, 'http://localhost:8888/');
            curl_setopt($curl, CURLOPT_VERBOSE, true);
        }

        $result = curl_exec($curl);

        curl_close($curl);

        return trim($result);
    }



    private static function normalizeSimpleXML ( $obj, &$result )
    {
        $data = $obj;
        if ( is_object($data) )
        {
            $data = get_object_vars($data);
        }
        if ( is_array($data) )
        {
            foreach ( $data as $key => $value )
            {
                $res = null;
                self::normalizeSimpleXML($value, $res);
                if ( ($key == '@attributes') && ($key) )
                {
                    $result = $res;
                }
                else
                {
                    $result[$key] = $res;
                }
            }
        }
        else
        {
            $result = $data;
        }
    }



    /**
     * Converts XML to JSON (ignoring attributes).
     *
     * @param $xml
     *
     * @return false|string
     */
    public static function xmlDecodeComplex ( $xml )
    {
        self::normalizeSimpleXML(simplexml_load_string($xml), $result);

        return $result;

        //return json_encode($result);
    }


    public static function xmlDecode ( $xml )
    {
        $object = simplexml_load_string($xml);
        return @json_decode(@json_encode($object), 1);
    }
}
