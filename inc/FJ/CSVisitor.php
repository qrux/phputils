<?php
/**
 * Copyright (c) 2012-2020 Troy Wu
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



interface CSVisitor
{
    /**
     * NOTE - "Main" method, called for each CSV line tokenized.
     *
     * @param int        $lineIndex - Index of line, inclusive of all lines.
     * @param string[]   $tokens    - Array of strings.
     * @param array|bool $columns   - (false) if no column names; otherwise, array of columns names
     *
     * @return mixed
     */
    function parse ( $lineIndex, $tokens, $columns = false );


    /**
     * @param $lineIndex
     *
     * @return mixed
     */
    function ante ( $lineIndex );


    /**
     * @param $lineIndex
     * @param $tokens
     *
     * @return mixed
     */
    function parseComment ( $lineIndex, $tokens );


    /**
     * @param $lineIndex
     *
     * @return mixed
     */
    function post ( $lineIndex );


    /**
     * @return mixed
     */
    function finish ();
}
