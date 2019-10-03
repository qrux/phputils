<?php
/**
 * Copyright (c) 2012 Troy Wu
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright notice,
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
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */


require_once(__DIR__ . "/../../inc/FJ/autoloader.php");


// NOTE - We're not using this, because each connection will be separately managed.
//session_start();

class AjaxCalls
{
    /**
     * @param Ajax $ajax
     *
     * @return Ajax
     */
    public static function add ( $ajax )
    {
        $a = $ajax->testAbortBoth("a");
        $b = $ajax->testAbortBoth("b");

        $a = (int)$a;
        $b = (int)$b;

        $sum = $a + $b;

        clog("API/add - sum", $sum);

        $ajax->win("Added");
        $ajax->set("sum", $sum);

        return $ajax;
    }
}

//
// -----=====[ MAIN ]=====-----
//
$ajax                  = new Ajax();
$isHTTPS               = $ajax->isHTTPS();
$isLocalhost           = $ajax->isLocalhost();
$isProd                = !$isLocalhost;
$isntSecure            = !$isHTTPS;
$shouldBeSecureButIsnt = $isProd && $isntSecure;

/*
 * Comment out the following should-be-secure-but-isn't block
 * to disable HTTPS check (maybe b/c Android clients are being retarded).
 */
if ( $shouldBeSecureButIsnt )
{
    clog("API", "Dropping request because not HTTPS . ");
    echo "Hey--you shouldn't be here naked!<br/>\n";
    exit();
}

$isOkay = $ajax->testBoth("method");
if ( false === $isOkay )
{
    clog("API", "Dropping request because no method.");
    echo "Hey--you shouldn't be here without knowing what you want!<br />\n";
    exit();
}

$method = $ajax->testAbortBoth("method");
$method = FJ::dashesToCamelCase($method);

clog("-----=====[API - $method()] =====-----", $_POST);

$callable   = array("AjaxCalls", $method);
$isCallable = is_callable($callable);

if ( !$isCallable )
{
    $ajax->fail("Unknown API method($method); aborting.");
}
else
{
    $ajax = call_user_func($callable, $ajax);
}

$ajax->respond();
