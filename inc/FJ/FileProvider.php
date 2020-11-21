<?php



namespace FJ;



interface FileProvider
{
    const DEBUG_FILES         = false;
    const DEBUB_FILES_VERBOSE = false;

    const DEBUG_S3         = true;
    const DEBUG_S3_VERBOSE = false;



    public function init ( $params = false );
    public function ls ( $params = false );
    public function write ( $path, $data, $meta = false );
    public function read ( $path );
}
