<?php



namespace FJ;



use Exception;



class FileProviderLocal extends FileProviderBase
{
    private $parent = false;
    private $dir    = false;



    function __construct ( $params )
    {
        $parent = $params["file_location"];
        $dir    = $params['file_bucket'];

        $this->parent = $parent;
        $this->dir    = $parent . DIRECTORY_SEPARATOR . $dir;
    }



    /**
     * @param $params array|bool
     *
     * @throws Exception
     */
    public function init ( $params = false )
    {
        clog("FP.init() params", $params);

        if ( false !== $params )
        {
            if ( array_key_exists("dir", $params) )
            {
                $this->dir = $params["dir"];
            }

            if ( array_key_exists("bucket", $params) )
            {
                $bucket    = $params['bucket'];
                $this->dir = $this->parent . DIRECTORY_SEPARATOR . $bucket;
            }
        }

        if ( false === $this->dir )
        {
            Log::error("No directory specified; aborting.");
            throw new Exception("No directory specified for local FileProvider.");
        }

        if ( !file_exists($this->dir) )
        {
            Log::error("[ " . $this->dir . " ] does not exist; aborting.");
            throw new Exception("Specified path does not exist (local).");
        }

        if ( !is_dir($this->dir) )
        {
            Log::error("[ " . $this->dir . " ] is not a directory; aborting.");
            throw new Exception("Specified path is not a directory (local).");
        }

        if ( !is_readable($this->dir) )
        {
            Log::warn("[ " . $this->dir . " ] is NOT readable.");
        }

        if ( !is_writeable($this->dir) )
        {
            Log::warn("[ " . $this->dir . " ] is NOT writeable.");
        }

        clog("FP.init()", "FileProvider (local - {$this->dir}) successfully init'ed.");
    }



    public function ls ( $params = false )
    {
        $prefix = (false === $params) ? false : $params['prefix'];

        //
        // NOTE - ...otherwise, look locally.
        //
        $pattern = $this->getLocalPathFromKey($prefix . "*");
        $list    = glob($pattern);

        clog("pattern", $pattern);
        if ( self::DEBUB_FILES_VERBOSE ) clog("glob list", $list);

        $files = [];

        foreach ( $list as $entry )
        {
            $file = basename($entry);

            // if ( $this->doesNameConform($file) ) $files[] = $file;

            $files[] = $file;
        }

        if ( self::DEBUG_FILES ) clog("files", $files);

        return $files;
    }



    public function write ( $path, $data, $meta = false )
    {
        $data = trim($data) . "\n";

        $wholePath = $this->getLocalPathFromKey($path);
        file_put_contents($wholePath, $data);
    }



    public function read ( $path )
    {
        $wholePath = $this->getLocalPathFromKey($path);

        if ( self::DEBUG_S3 ) clog("FP.read()", $wholePath);

        if ( !file_exists($wholePath) ) return false;

        $data = file_get_contents($wholePath);

        return trim($data);
    }



    private function getLocalPathFromKey ( $filename )
    {
        return $this->dir . DIRECTORY_SEPARATOR . $filename;
    }
}
