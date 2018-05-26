<?php
/**
 * Copyright (c) 2012 Troy Wu
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

function readSinglePasswordFromCLI ( $mesg )
{
    do
    {
        // Get password without echo'ing.
        echo "$mesg: ";
        system('stty -echo');
        $passwd = trim(fgets(STDIN));
        system('stty echo');
        // add a new line since the users CR didn't echo
        echo "\n";
    }
    while ( 0 == strlen($passwd) );

    return $passwd;
}


function readPasswordFromCLI ( $mesg )
{
    $counter = 1;
    do
    {
        $mesg2 = (1 == $counter) ? $mesg : "$counter) $mesg";

        $p1    = readSinglePasswordFromCLI($mesg2);
        $p2    = readSinglePasswordFromCLI($mesg2 . " (again)");
        $match = (0 === strcmp($p1, $p2));
        if ( !$match )
        {
            clog("  Sorry, both those passwords didn't match.  Try again, please.");
        }
    }
    while ( !$match );

    return $p1;
}


function _FJ_CLI_ENTRY_POINT ( $argc, $argv )
{
    // NOTE - How to properly detect command line?
    // NOTE - This looks like a trick to use PHP.ini, and setting the value of highlight.bg...
    /*
    $allVars = ini_get_all();
    $var     = 'highlight.bg';
    $val     = '#gggggg';

    if ( isCommandLineInterface() )
    {
        $varVal = $allVars[$var];
        if ( isset($varVal['local_value']) && ($val !== $varVal['local_value']) )
        {
            main($argc, $argv);
        }
        main($argc, $argv);
    }
    */

    exit(  isCLI() && main($argc, $argv)  );
}

define("SHOULD_CLOG_OUTPUT_REMOTE_ADDR_INFO", false);

clog("About to CLI...");

_FJ_CLI_ENTRY_POINT($argc, $argv);
// This enters the CLI script, which must be a shebang'ed PHP script with a main($argc,$agv) function defined.
