<?php



namespace FJ;



abstract class CSVisitorBasic implements CSVisitor
{
    /**
     * @param $lineIndex
     *
     * @return mixed
     */
    function ante ( $lineIndex ) { return false; }

    /**
     * @param $lineIndex
     * @param $tokens
     *
     * @return mixed
     */
    function parseComment ( $lineIndex, $tokens ) { return false; }

    /**
     * @param $lineIndex
     *
     * @return mixed
     */
    function post ( $lineIndex ) { return false; }

    /**
     * @return mixed
     */
    function finish () { return false; }
}
