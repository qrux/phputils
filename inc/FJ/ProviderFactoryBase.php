<?php



namespace FJ;



use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Aws\SecretsManager\SecretsManagerClient;
use Exception;




/**
 * Class ProviderFactoryBase
 *
 * Old ProviderFactory is stateless, allowing dynamic loading
 * if config files change between calls.
 *
 * But this is unlikely to be necessary, and a page-load
 * (or a new CLI invocation) will reload the config files anyway.
 *
 * So, remove statelessness, fail-fast, and cache data,
 * which is only in-memory for the duration of the load/call.
 *
 *
 * @package FJ
 */
abstract class ProviderFactoryBase
{
    const DEBUG_CONFIG_INFO     = true;
    const DEBUG_DB_CONN         = true;
    const DEBUG_DB_CONN_VERBOSE = false;

    const DEBUG_CREDS_DANGEROUS = false; // DANGER - In __PRODUCTION__, this must be set to (false)!!!!!



    /**
     * @return string - Sets the "name" of the "app", the intent of which
     *                is just to give a unique prefix to the files and dirs.
     */
    abstract public function getAppName ();

    /**
     * @return array - Returns the "default" configuration parameters,
     *               for when the cloud configuration should be used.
     *               Typically, the cloud config is used in prod, and the
     *               local config used for dev.
     */
    abstract protected function setAppDefaults ();



    const FILE_TYPE_CONFIG      = "config";
    const PATH_COMPONENT_CONFIG = "/" . self::FILE_TYPE_CONFIG . "/";

    const FILE_TYPE_AUTH      = "auth";
    const PATH_COMPONENT_AUTH = "/" . self::FILE_TYPE_AUTH . "/";


    const DEFAULT_LOCAL_DIRS = [
        //"/Volumes/data-ici/",
        "/Users/srv/",
        "/Users/",
        "/srv/",
    ];



    private $root    = false;
    private $isLocal = false;



    function __construct ( $root = false )
    {
        // If $dir is not false, prepend dir to array.

        // 1. Try finding file.
        // 2. If fails, set default from concrete implementing class.

        //
        // If arg is given, use as first dir to try; otherwise, ignore.
        //
        if ( false !== $root && !FJ::endsWith("/", $root) ) $root .= "/";

        $localDirs = false === $root ? [] : [ $root ];
        $localDirs = array_merge($localDirs, self::DEFAULT_LOCAL_DIRS);


        foreach ( $localDirs as $dir )
        {
            clog("Looking for meta root [ $dir ]...");

            if ( $this->hasRootConfigFile($dir) )
            {
                $this->root    = $dir;
                $this->isLocal = true;
                break;
            }
        }

        if ( $this->isLocal )
        {
            clog("Using local config at [ {$this->root} ]...");
            $this->loadLocalConfig();
        }
        else
        {
            clog("Using APP DEFAULTS (see concrete implementing class)...");
            $this->setAppDefaults();
        }
    }



    private function hasRootConfigFile ( $dir )
    {
        if ( !file_exists($dir) || !is_dir($dir) )
        {
            return false;
        }


        $file = $this->getConfigFilePath($dir);

        return file_exists($file) && is_file($file) && is_readable($file);
    }



    private function loadLocalConfig ()
    {
        $conf   = self::loadMetaFile($this->getConfigFilePath($this->root), true); // ____DO____ Dump JSON
        $auth   = self::loadMetaFile($this->getAuthFilePath($this->root));         // __DO NOT__ Dump JSON.
        $params = array_merge($conf, $auth);

        return $params;
    }



    private static function loadMetaFile ( $file, $shouldDump = false )
    {
        if ( !is_readable($file) )
        {
            redlog("Could not read config file: $file");
            return [];
        }

        $json = file_get_contents($file);

        if ( self::DEBUG_CONFIG_INFO && true === $shouldDump ) clog("meta(json)", $json);

        return FJ::jsDecode($json);
    }



    private function getFileName ( $file ) { return $this->getAppName() . "-" . $file . ".js"; }
    private function getComponentDir ( $dir, $component ) { return $dir . $this->getAppName() . $component; }

    private function getConfigFilePath ( $dir ) { return $this->getComponentDir($dir, self::PATH_COMPONENT_CONFIG) . $this->getFileName(self::FILE_TYPE_CONFIG); }
    private function getAuthFilePath ( $dir ) { return $this->getComponentDir($dir, self::PATH_COMPONENT_AUTH) . $this->getFileName(self::FILE_TYPE_AUTH); }
}
