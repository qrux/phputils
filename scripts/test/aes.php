#! /usr/bin/php
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

require_once(__DIR__ . "/../../inc/fj-autoloader.php");

//require_once(__DIR__ . "/../../inc/phpseclib/bootstrap.php");


use \FJ\FJ;
use \FJ\Log;
use function \FJ\clog;
use function \FJ\cclog;
use \phpseclib\Crypt\AES;


/**
 * @param $argc
 * @param $argv
 *
 * @return int
 */
function main ( $argc, $argv )
{
    // Test using NIST test vectors.

    $plainHex1 = "6bc1bee22e409f96e93d7e117393172a";
    $plainHex2 = "ae2d8a571e03ac9c9eb76fac45af8e51";
    $plainHex3 = "30c81c46a35ce411e5fbc1191a0a52ef";
    $plainHex4 = "f69f2445df4f9b17ad2b417be66c3710";
    $plainHex  = $plainHex1 . $plainHex2 . $plainHex3 . $plainHex4;
    $plain     = hex2bin($plainHex);

    $cipherHex1         = "601ec313775789a5b7a7f504bbf3d228";
    $cipherHex2         = "f443e3ca4d62b59aca84e990cacaf5c5";
    $cipherHex3         = "2b0930daa23de94ce87017ba2d84988d";
    $cipherHex4         = "dfc9c58db67aada613c2dd08457941a6";
    $cipherHex          = $cipherHex1 . $cipherHex2 . $cipherHex3 . $cipherHex4;
    $expectedCiphertext = hex2bin($cipherHex);

    $keyHex = "603deb1015ca71be2b73aef0857d77811f352c073b6108d72d9810a30914dff4";
    $key    = hex2bin($keyHex);

    $ctrHex = "f0f1f2f3f4f5f6f7f8f9fafbfcfdfeff";
    $ctr    = hex2bin($ctrHex);

    clog("       key (hex)", $keyHex);
    clog("       ctr (hex)", $ctrHex);
    clog(" plaintext (hex)", $plainHex);

    // test_mcrypt(...); // No longer using this, since Rijndal != AES.

    test1($key, $ctr, $plain, $expectedCiphertext);
    test2($key, $ctr, $plain, $expectedCiphertext);
    test3($key, $plain);
    test4($key, $plain);

    return 0;
}


/**
 * Uses phpseclib directly.
 *
 * @param $key
 * @param $iv
 * @param $plaintext
 * @param $expectedCiphertext
 *
 * @return bool - (true) if (plain === recovered) && (cipher === expected); (false) otherwise.
 */
function test1 ( $key, $iv, $plaintext, $expectedCiphertext )
{
    $aes = new AES(FJ::FJ_DEFAULT_AES_MODE);
    //$cipher->setPassword('whatever');
    $aes->setKey($key);
    $aes->setIV($iv);

    // the following does the same thing:
    //$cipher->setPassword('whatever', 'pbkdf2', 'sha1', 'phpseclib/salt', 1000, 256 / 8);
    //$cipher->setIV('...'); // defaults to all-NULLs if not explicitely defined

    //$size      = 10 * 1024;
    //$plaintext = str_repeat('a', $size);

    $ciphertext = $aes->encrypt($plaintext);
    $recovered  = $aes->decrypt($ciphertext);

    return verify("test1", $plaintext, $expectedCiphertext, $ciphertext, $recovered);
}


/**
 * Uses FJ-refactored AES calls.
 *
 * @param $key
 * @param $iv
 * @param $plaintext
 * @param $expectedCiphertext
 *
 * @return bool - (true) if (plain === recovered) && (cipher === expected); (false) otherwise.
 */
function test2 ( $key, $iv, $plaintext, $expectedCiphertext )
{
    $ciphertext = FJ::encrypt($key, $iv, $plaintext);
    $recovered  = FJ::decrypt($key, $iv, $ciphertext);

    return verify("test2", $plaintext, $expectedCiphertext, $ciphertext, $recovered);
}


/**
 * Uses FJ-refactored AES calls, testing if IV matters.
 *
 * Tests MISMATCHED-CTR-IV (last byte different) -- OBV. DOES NOT WORK!
 *
 * @param $key
 * @param $plaintext
 *
 * @return bool - (true) if (plain === recovered) && (cipher === expected); (false) otherwise.
 */
function test3 ( $key, $plaintext )
{
    $ctrHex1 = "f0f1f2f3f4f5f6f7f8f9fafbfcfdfe00";
    $ctrHex2 = "f0f1f2f3f4f5f6f7f8f9fafbfcfdfe01";

    $ctr1 = hex2bin($ctrHex1);
    $ctr2 = hex2bin($ctrHex2);

    $ciphertext = FJ::encrypt($key, $ctr1, $plaintext);
    $recovered  = FJ::decrypt($key, $ctr2, $ciphertext);

    return verify("test3", $plaintext, false, $ciphertext, $recovered, false);
}


/**
 * Uses FJ-refactored AES calls.
 *
 * Tests MATCHING-BUT-BAD-CTR-IV (wrong bit-length) -- WORKS!
 *
 * @param $key
 * @param $plaintext
 *
 * @return bool - (true) if (plain === recovered) && (cipher === expected); (false) otherwise.
 */
function test4 ( $key, $plaintext )
{
    $iv = "hello";

    try
    {
        $ciphertext = FJ::encrypt($key, $iv, $plaintext);
    }
    catch ( LengthException $e )
    {
        $ciphertext = "<exception-occurred-no-ciphertext>";
        Log::warn("Expected FJ::encrypt() failure using IV with BAD LENGTH ($iv)");
    }


    try
    {
        $recovered = FJ::decrypt($key, $iv, $ciphertext);
    }
    catch ( LengthException $e )
    {
        $recovered = "<exception-occurred-no-decrypted-text>";
        Log::warn("Expected FJ::decrypt() failure using IV with BAD LENGTH ($iv)");
    }

    return verify("test4", $plaintext, false, $ciphertext, $recovered, false);
}


/**
 * Checks results, prints output.
 *
 * @param $plaintext
 * @param $expectedCiphertext
 * @param $ciphertext
 * @param $recovered
 * @param $isExpectedPass - (true) if expected-to-pass; (false) otherwise.
 *
 * @return bool - (true) if (plain === recovered) && (cipher === expected); (false) otherwise.
 */
function verify ( $mesg, $plaintext, $expectedCiphertext, $ciphertext, $recovered, $isExpectedPass = true )
{

    $plainHex = bin2hex($plaintext);
    $outHex   = bin2hex($ciphertext);

    // NOTE - Not always comparing to known encrypted result.
    if ( false !== $expectedCiphertext )
    {
        $cipherHex = bin2hex($expectedCiphertext);

        clog("$mesg -             expected ciphertext (hex)", $cipherHex);
        clog("$mesg - Pure-PHP AES (phpseclib) output (hex)", $outHex);

        clog("$mesg -   expected ciphertext length", strlen($expectedCiphertext));
        clog("$mesg -     actual ciphertext length", strlen($ciphertext));

        $isEncryptionWorking = ($ciphertext === $expectedCiphertext);
        //$isEncryptionWorking = false;

        clog("$mesg - Pure-PHP AES (phpseclib) encryption worked?", $isEncryptionWorking);

        if ( $isExpectedPass )
        {
            if ( !$isEncryptionWorking )
            {
                Log::error("$mesg - Pure-PHP AES (phpseclib) ENCRYPTION not working.");
            }
        }
        else
        {
            if ( $isEncryptionWorking )
            {
                Log::error("$mesg - Pure-PHP AES (phpseclib) ENCRYPTION not working.");
            }
        }
    }

    $recHex              = bin2hex($recovered);
    $isDecryptionWorking = ($recovered === $plaintext);
    //$isDecryptionWorking = false;

    clog("$mesg -                  expected recovered-plaintext (hex)", $recHex);
    clog("$mesg - Pure-PHP AES (phpseclib) recovered-plainetext (hex)", $plainHex);

    determineStatus($mesg, "Pure-PHP AES (phpseclib) decryption worked?", $isDecryptionWorking, $isExpectedPass);


    clog("$mesg - Pure-PHP AES (phpseclib) decryption worked?", $isDecryptionWorking);

    if ( $isExpectedPass )
    {
        if ( !$isDecryptionWorking )
        {
            Log::error("$mesg - Pure-PHP AES (phpseclib) DECRYPTION not working.");
        }
    }
    else
    {
        if ( $isDecryptionWorking )
        {
            Log::error("$mesg - Pure-PHP AES (phpseclib) DECRYPTION not working.");
        }
        else
        {
            clog("$mesg - Expected failure");
        }
    }

    if ( false === $expectedCiphertext )
    {
        return $isDecryptionWorking;
    }
    else
    {
        return $isEncryptionWorking && $isDecryptionWorking;
    }
}


function determineStatus ( $mesg, $base, $actual, $expected )
{
    $isGood = ($expected === $actual);

    if ( $isGood && $actual )
    {
        clog("$mesg - $base", "PASS");
    }
    else
    {
        clog("$mesg - $base", "EXPECTED-FAIL");
    }
}


/**
 * Uses mcrypt, which does not support AES.
 *
 * @return bool - (true) if (plain === recovered) && (cipher === expected); (false) otherwise.
 */
function test_mcrypt ()
{
    //clog("ciphertext (hex)", $cipherHex);

    /*
    $ivSize128  = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, 'ctr');
    $ivSize192  = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_192, 'ctr');
    $ivSize256  = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, 'ctr');
    $isCTRBlock = mcrypt_module_is_block_algorithm_mode('ctr');

    clog("AES-128 - IV (CTR) length", $ivSize128);
    clog("AES-192 - IV (CTR) length", $ivSize192);
    clog("AES-256 - IV (CTR) length", $ivSize256);
    clog("CTR (per NIST)     length", strlen($ctr));
    clog("CTR is block mode in PHP?", $isCTRBlock);

    $td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
    mcrypt_generic_init($td, $key, $ctr);
    $out    = mcrypt_generic($td, $plain);
    $outHex = bin2hex($out);

    clog("       key (hex)", $keyHex);
    clog("       ctr (hex)", $ctrHex);
    clog(" plaintext (hex)", $plainHex);
    clog("ciphertext (hex)", $cipherHex);
    clog("    output (hex)", $outHex);

    if ( $cipher == $out )
    {
        clog("mcrypt (Rijndal-256 used as AES-256) encryption worked?", true);
    }
    else
    {
        clog("mcrypt (Rijndal-256 used as AES-256) encryption worked?", false);
    }
    */

    return false;
}


/*
 * ########################################################################
 * ########################################################################
 * ########################################################################
 * ########################################################################
 * ########################################################################
 *
 * NOTE - CLI Entry Point!
 *
 * ########################################################################
 * ########################################################################
 * ########################################################################
 * ########################################################################
 * ########################################################################
 */
main($argc, $argv);
