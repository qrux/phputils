<?php



namespace FJ;



class FileLooper
{
    const CURRENT_DIR = ".";
    const PARENT_DIR  = "..";



    /**
     * @var FileVisitor
     */
    private $visitor;



    /**
     * FileLooper constructor.
     *
     * @param $fileVisitor FileVisitor
     */
    function __construct ( $fileVisitor )
    {
        $this->visitor = $fileVisitor;
    }



    function loop ( $path )
    {
        if ( $handle = opendir($path) )
        {
            while ( false !== ($entry = readdir($handle)) )
            {
                switch ( $entry )
                {
                    case self::CURRENT_DIR:
                    case self::PARENT_DIR:
                        continue;

                    default:
                        $this->visitor->visit($path, $entry);
                }
            }
            closedir($handle);
        }
    }
}
