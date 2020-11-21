<?php



namespace FJ;



abstract class FileProviderBase implements FileProvider
{
    private $filePrefix = false;
    private $fplen      = 0;



    protected function doesNameConform ( $key )
    {
        if ( false !== $this->filePrefix )
        {
            $lower  = strtolower($key);
            $target = substr($lower, 0, $this->fplen);
            return (0 == strncmp($target, $this->filePrefix, $this->fplen));
        }

        return true;
    }
}
