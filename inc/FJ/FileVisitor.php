<?php



namespace FJ;



interface FileVisitor
{
    /**
     * Perform action.  Keep in mind that filename may itself reference a directory.
     *
     * @param $dir       string - Directory representing path to file (dirname).
     * @param $entryName string - Entry name (basename).
     *
     * @return mixed
     */
    function visit ( $dir, $entryName );
}
