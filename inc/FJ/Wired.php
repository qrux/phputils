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


/**
 * An appropriate implementation will allow flattening and unflattening
 * of objects, perhaps to be used for marshalling, persistence, or even
 * debugging.
 */
interface Wired
{
    /**
     * ****************************************************************
     * Indexed array (i.e., numerically-indexed, non-associative)
     * representation of this object.
     *
     * @return array - Indexed array (with numeric indices) of contents.
     * @abstract
     * ****************************************************************
     */
    function toArray ();


    /**
     * ****************************************************************
     * Associative-array representation of this object.
     *
     * @return array - Associative array (i.e., hash) of contents.
     * @abstract
     * ****************************************************************
     */
    function toHash ();


    /**
     * ****************************************************************
     * Returns an "flattened" encoding (i.e., a regular associative
     * array, not an object with potentially opaque fields)
     * of this object, suitable for subsequent pass through
     * FJ::json_encode().
     *
     * Inverse of newInstance().
     *
     * @return array - Associative array of contents.
     *
     * @see newInstance()
     * @abstract
     * ****************************************************************
     */
    function toStore ();


    /**
     * ****************************************************************
     * Single-string representation of this object.  Also implements
     * the MAGIC METHOD for string-conversion.
     *
     * @return string
     * @abstract
     * ****************************************************************
     */
    function __toString ();


    /**
     * ****************************************************************
     * Creates an instance of implementing (concrete) subclass from
     * its "flattened" encoding (i.e., a regular associative array, not
     * an object with potentially opaque fields).  The input is likely
     * to have come from (though this is only speculative) a call of
     * FJ::json_decode($str, true).
     *
     * Inverse of toStore().
     *
     * @param array $flat - "Flattened" encoding.
     *
     * @return Wired
     *
     * @see toStore()
     * @abstract
     * ****************************************************************
     */
    static function newInstance ( $flat );
}
