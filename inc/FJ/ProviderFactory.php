<?php



namespace FJ;



use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Aws\SecretsManager\SecretsManagerClient;
use Exception;



abstract class ProviderFactory
{
    const DEBUG_CONFIG_INFO     = true;
    const DEBUG_DB_CONN         = true;
    const DEBUG_DB_CONN_VERBOSE = false;

    const DEBUG_CREDS_DANGEROUS = false; // DANGER - In __PRODUCTION__, this must be set to (false)!!!!!



    const CREDENTIALS_ARRAY_KEY = "credentials";

    const KEY_AUTH_FILE   = "auth_file";
    const KEY_AUTH_BUCKET = "auth_bucket";
    const KEY_AUTH_KEY    = "auth_key";

    const KEY_FILE_LOCATION = "file_location";
    const KEY_FILE_BUCKET   = "file_bucket";

    const AWS_ACCESS_ARRAY_KEY  = "aws_access_key_id";
    const AWS_SECRET_ARRAY_KEY  = "aws_secret_access_key";
    const AWS_REGION_ARRAY_KEY  = "aws_region";
    const AWS_VERSION_ARRAY_KEY = "aws_version";

//    const FJ_DEFAULT_FJ_FILE_PREFIX        = false;
//    const FJ_FILE_PREFIX_ARRAY_KEY         = "file_prefix";
//    const FJ_BLOCKCHAIN_PROVIDER_ARRAY_KEY = "blockchain_name";

    const DEFAULT_FJ_AWS_REGION  = "eu-west-1";
    const DEFAULT_FJ_AWS_VERSION = "latest";

    const PROVIDER_LOCAL = "local";
    const PROVIDER_PROXY = "proxy";
    const PROVIDER_CLOUD = "cloud";

    const DB_HOST_ARRAY_KEY = "db_host_";
    const DB_PORT_ARRAY_KEY = "db_port_";
    const DB_NAME_ARRAY_KEY = "db_name_";
    const DB_USER_ARRAY_KEY = "db_user_";
    const DB_PASS_ARRAY_KEY = "db_password_";

    const DEFAULT_LOCAL_PATH_PREFIX = "/srv/";

    const CONFIG_TYPE_BASE      = "config";
    const FILE_CONFIG           = self::CONFIG_TYPE_BASE;
    const PATH_COMPONENT_CONFIG = "/" . self::FILE_CONFIG . "/";

    const CONFIG_TYPE_CREDS    = "auth";
    const FILE_CREDS           = self::CONFIG_TYPE_CREDS;
    const PATH_COMPONENT_CREDS = "/" . self::CONFIG_TYPE_CREDS . "/";

    const PROVIDER_TYPE_FILE = "file";
    const PROVIDER_TYPE_DB   = "db";



    private $localPathPrefix = self::DEFAULT_LOCAL_PATH_PREFIX;



    abstract public function getAppName ();
    abstract protected function setConfigDefaults ();



    function __construct ( $localPathPrefix = self::DEFAULT_LOCAL_PATH_PREFIX )
    {
        $this->localPathPrefix = $localPathPrefix;
    }



    private function loadConfigParams ()
    {
        return $this->hasLocalConfig()
            ? $this->loadLocalConfig()
            : $this->setConfigDefaults();
    }



    public function getProviderValue ( $provider, $configKeyShort )
    {
        $params = $this->loadConfigParams();

        $provKey = $provider . "_provider";

        if ( !array_key_exists($provKey, $params) ) return false;

        $prov = $params[$provKey];

        $configKey = $provider . "_" . $configKeyShort . "_" . $prov;

        if ( !array_key_exists($configKey, $params) ) return false;

        return $params[$configKey];
    }



    private function isUsingLocalProvider ( $providerType )
    {
        $key = $providerType . "_provider";
        return $this->matches($key, self::PROVIDER_LOCAL);
    }



    private function isUsingCloudProvider ( $providerType )
    {
        $key = $providerType . "_provider";
        return $this->matches($key, self::PROVIDER_CLOUD);
    }



    private function matches ( $key, $targetValue )
    {
        return $this->has($key) ? $targetValue === $this->get($key) : false;
    }



    public function has ( $key )
    {
        $params = $this->loadConfigParams();
        $val    = array_key_exists($key, $params);
        $params = false;
        return $val;
    }



    public function get ( $key )
    {
        $params = $this->loadConfigParams();
        $val    = array_key_exists($key, $params) ? $params[$key] : null;
        $params = false;
        return $val;
    }



    private function getConfigFilePath () { return $this->getPathPrefix(self::PATH_COMPONENT_CONFIG) . $this->getPathFilename(self::FILE_CONFIG); }
    private function getAuthFileName () { return $this->getPathFilename(self::FILE_CREDS); }
    private function getAuthFilePath () { return $this->getPathPrefix(self::PATH_COMPONENT_CREDS) . $this->getAuthFileName(); }
    private function getPathPrefix ( $component ) { return $this->localPathPrefix . $this->getAppName() . $component; }
    private function getPathFilename ( $file ) { return $this->getAppName() . "-" . $file . ".js"; }



    private function hasLocalConfig ()
    {
        return file_exists($this->getConfigFilePath());
    }



    /**
     * @return AuthProvider
     */
    public function getAuthProvider ()
    {
        //$params = $this->loadConfigParams();

        $isAuthProviderLocal = $this->isUsingLocalProvider(self::CONFIG_TYPE_CREDS);

        clog("is-auth-local", $isAuthProviderLocal);

        $authParams = $isAuthProviderLocal
            ? $this->getAuthParamsLocal()
            : $this->getAuthParamsAWS();

        //clog("auth-params", $authParams);
        clog("Got parameters; instantiating AuthProvider...");

        $authProvider = $isAuthProviderLocal
            ? new AuthProviderLocal($authParams)
            : new AuthProviderAWS($authParams);

        clog("Instantiated AuthProvider instance", get_class($authProvider));

        $authParams = false;
        $params     = false;

        return $authProvider;
    }



    private function getAuthParamsLocal ()
    {
        if ( !$this->has(self::KEY_AUTH_FILE) )
        {
            Log::error("Cannot find auth file; aborting.");
            return [];
        }

        $authFilePath = $this->get(self::KEY_AUTH_FILE);
        $params       = [
            self::KEY_AUTH_FILE => $authFilePath,
        ];
        return $params;
    }




    private function getAuthParamsAWS ()
    {
        if ( !$this->has(self::KEY_AUTH_BUCKET) )
        {
            Log::error("Cannot find auth bucket; aborting.");
            return [];
        }
        if ( !$this->has(self::KEY_AUTH_KEY) )
        {
            Log::error("Cannot find auth key; aborting.");
            return [];
        }

        $authBucket = $this->get(self::KEY_AUTH_BUCKET);
        $authKey    = $this->get(self::KEY_AUTH_KEY);

        clog("bucket", $authBucket);
        clog("   key", $authKey);

        $s3 = $this->getS3Client();

        $params = [
            "s3"                  => $s3,
            self::KEY_AUTH_BUCKET => $authBucket,
            self::KEY_AUTH_KEY    => $authKey,
        ];
        return $params;
    }



    /**
     * @return array - JSON object containing both the config and auth info.
     */
    private function loadLocalConfig ()
    {
        $configFilePath = $this->getConfigFilePath();
        $authFilePath   = $this->getAuthFilePath();

        clog("config file path", $configFilePath);
        clog("  auth file path", $authFilePath);

        $conf = self::loadConfigFile($configFilePath);
        $auth = self::loadConfigFile($authFilePath);

        clog("conf", $conf);
        clog("auth", $auth);

        $params = array_merge($conf, $auth);

        return $params;
    }



    private static function loadConfigFile ( $file )
    {
        if ( !is_readable($file) )
        {
            redlog("Could not read config file: $file");
            return [];
        }

        $json = file_get_contents($file);
        $json = trim($json);

        if ( self::DEBUG_CONFIG_INFO && self::doesNotLookLikeAuthFile($file) ) clog($file . "(json)", $json);

        $conf = FJ::jsDecode($json);

        if ( self::DEBUG_CONFIG_INFO ) clog($file . "(obj)", $conf);

        return $conf;
    }



    private static function looksLikeAuthFile ( $file )
    {
        $sub = "auth";
        return false !== strstr($file, $sub);
    }
    private static function doesNotLookLikeAuthFile ( $file ) { return !self::looksLikeAuthFile($file); }



    /**
     * If this is running on a local machine with a config file,
     * use the credentials in the config file; otherwise, NOTE: DO NOTHING.
     *
     * When "nothing" is done, then allow AWS Client libraries to try to
     * pickup the role credentials.  This will work on EC2, and with the
     * command line.
     *
     * @param array|bool $params
     *
     * @return array
     */
    private function getCredsAWS ( $params = false )
    {
        if ( false === $params ) $params = [];

        $config = false;

        if ( $this->hasLocalConfig() )
        {
            $config = $this->loadLocalConfig();

            $access = $config[self::AWS_ACCESS_ARRAY_KEY];
            $secret = $config[self::AWS_SECRET_ARRAY_KEY];

            clog(self::AWS_ACCESS_ARRAY_KEY, $access);

            try
            {
                $params[self::CREDENTIALS_ARRAY_KEY] = [
                    'key'    => $access,
                    'secret' => $secret,
                ];

                $access = false;
                $secret = false;
            }
            catch ( \Exception $e )
            {
                clog($e);
                clog("Could not initialize local AWS credentials . ");
            }
        }

        $params['region']  = self::getAWSRegion($config);
        $params['version'] = self::getAWSVersion($config);

        if ( self::DEBUG_CREDS_DANGEROUS ) clog("getCredsAWS() FINAL -params", $params);

        return $params;
    }



    private static function getAWSRegion ( $config )
    {
        if ( false === $config ) return self::DEFAULT_FJ_AWS_REGION;

        return array_key_exists(self::AWS_REGION_ARRAY_KEY, $config)
            ? $config[self::AWS_REGION_ARRAY_KEY]
            : self::DEFAULT_FJ_AWS_REGION;
    }



    private static function getAWSVersion ( $config )
    {
        if ( false === $config ) return self::DEFAULT_FJ_AWS_VERSION;

        return array_key_exists(self::AWS_VERSION_ARRAY_KEY, $config)
            ? $config[self::AWS_VERSION_ARRAY_KEY]
            : self::DEFAULT_FJ_AWS_VERSION;
    }



    /**
     * @return S3Client|bool
     */
    private function getS3Client ()
    {
        $params = $this->getCredsAWS();
        try
        {
            $s3 = new S3Client($params);
        }
        catch ( \Exception $e )
        {
            clog($e);
            clog("Cannot get AWS S3 Client; returning(false) . ");
            $s3 = false;
        }

        $params = self::clearParams($params);

        return $s3;
    }



    /**
     * @return FileProvider
     */
    public function getFileProvider ()
    {
        $params = $this->loadConfigParams();

        $providerType = self::PROVIDER_TYPE_FILE;
        $isProvLocal  = $this->isUsingLocalProvider($providerType);

        clog("is $providerType local?", $isProvLocal);

        $provParams = $isProvLocal
            ? $this->getFileParamsLocal()
            : $this->getFileParamsAWS();

        $prov = $isProvLocal
            ? new FileProviderLocal($provParams)
            : new FileProviderAWS($provParams);

        $provParams = false;
        $params     = false;

        return $prov;
    }



    private function getFileParamsLocal ()
    {
        if ( !$this->has(self::KEY_FILE_LOCATION) )
        {
            Log::error("Cannot find auth file; aborting.");
            return [];
        }
        if ( !$this->has(self::KEY_FILE_BUCKET) )
        {
            Log::error("Cannot find auth bucket; aborting.");
            return [];
        }

        $authFilePath = $this->get(self::KEY_FILE_LOCATION);
        $authBucket   = $this->get(self::KEY_FILE_BUCKET);

        $params = [
            self::KEY_FILE_LOCATION => $authFilePath,
            self::KEY_FILE_BUCKET   => $authBucket,
        ];
        return $params;
    }



    private function getFileParamsAWS ()
    {
        $s3 = $this->getS3Client();

        $params = [
            "s3" => $s3,
        ];

        if ( $this->has(self::KEY_FILE_BUCKET) )
        {
            $params[self::KEY_FILE_BUCKET] = $this->get(self::KEY_FILE_BUCKET);
        }

        return $params;
    }



    /**
     * @return SecretsManagerClient|bool
     */
    private function getSecretsManagerClient ()
    {
        $params = $this->getCredsAWS();
        try
        {
            $secman = new SecretsManagerClient($params);
        }
        catch ( \Exception $e )
        {
            clog($e);
            clog("Cannot get AWS SecretsManager Client; returning(false)");
            $secman = false;
        }

        $params = self::clearParams($params);

        return $secman;
    }



    private static function clearParams ( $params )
    {
        unset($params[self::CREDENTIALS_ARRAY_KEY]);
        return false;
    }



    /**
     * @return PG
     *
     * @throws Exception
     */
    public function getDatabaseConnection ()
    {
        $params = $this->loadConfigParams();

        clog("params", $params);

        $provKey = self::PROVIDER_TYPE_DB . "_provider";
        $prov    = $params[$provKey];

        switch ( $prov )
        {
            case self::PROVIDER_CLOUD:
                $dbParams = $this->getRDSParams();
                if ( self::DEBUG_DB_CONN_VERBOSE ) clog("db (CLOUD) params", $dbParams);
                break;

            default:
                $dbParams = $this->getLocalDBParams($params, $prov);
                if ( self::DEBUG_DB_CONN_VERBOSE ) clog("db (local) params", $dbParams);
                break;
        }

        clog("DB params", $dbParams);

        $connString = $this->getDatabaseConnectionString($dbParams);

        $dbParams   = false;
        $pg         = new PG($connString); // <--------- MEAT
        $connString = false;

        return $pg;
    }



    private function getLocalDBParams ( $config, $provider )
    {
        $hostKey = self::DB_HOST_ARRAY_KEY . $provider;
        $portKey = self::DB_PORT_ARRAY_KEY . $provider;
        $nameKey = self::DB_NAME_ARRAY_KEY . $provider;

        $host = $config[$hostKey];
        $port = $config[$portKey];
        $name = $config[$nameKey];

        if ( self::PROVIDER_PROXY == $provider )
        {
            $params = self::getRDSParams();
            $user   = $params['username'];
            $pass   = $params['password'];
        }
        else
        {
            $userKey = self::DB_USER_ARRAY_KEY . $provider;
            $passKey = self::DB_PASS_ARRAY_KEY . $provider;

            $user = $config[$userKey];
            $pass = $config[$passKey];
        }

        return [
            'host'     => $host,
            'port'     => $port,
            'username' => $user,
            'password' => $pass,
            'dbname'   => $name,
        ];
    }



    private function getDatabaseConnectionString ( $dbParams )
    {

        if ( false === $dbParams ) return false;

        $host = $dbParams['host'];
        $port = $dbParams['port'];
        $user = $dbParams['username'];
        $pass = $dbParams['password'];
        $db   = $dbParams['dbname'];

        $dbstr = "host = $host port = $port dbname = $db user = $user";

        if ( self::DEBUG_DB_CONN ) clog("getDBConnectionString - DB conn str(no passwd)", $dbstr);

        $dbstr .= " password = $pass";

        return $dbstr;
    }



    /**
     * @return bool|array
     */
    private function getRDSParams ()
    {
        clog("getRDSParams() - Creating AWS SecretsManager Client", "ANTE");

        $client = $this->getSecretsManagerClient();

        clog("getRDSParams() - Creating AWS SecretsManager Client", "POST");

        if ( false === $client || !$client )
        {
            redlog("Cannot create SecretsManagerClient object; aborting");
            return false;
        }

        $secretName = 'dashboard';

        try
        {
            clog("get - db - secrets", "Getting secret [$secretName]...");

            $result = $client->getSecretValue(
                [
                    'SecretId' => $secretName,
                ]
            );
        }
        catch ( AwsException $e )
        {
            cclog(Log::TEXT_COLOR_BG_RED, "FAIL to get DB auth secrets . ");

            $error = $e->getAwsErrorCode();

            if ( $error == 'DecryptionFailureException' )
            {
                // Secrets Manager can't decrypt the protected secret text using the provided AWS KMS key.
                // Handle the exception here, and/or rethrow as needed.
            }
            if ( $error == 'InternalServiceErrorException' )
            {
                // An error occurred on the server side.
                // Handle the exception here, and/or rethrow as needed.
            }
            if ( $error == 'InvalidParameterException' )
            {
                // You provided an invalid value for a parameter.
                // Handle the exception here, and/or rethrow as needed.
            }
            if ( $error == 'InvalidRequestException' )
            {
                // You provided a parameter value that is not valid for the current state of the resource.
                // Handle the exception here, and/or rethrow as needed.
            }
            if ( $error == 'ResourceNotFoundException' )
            {
                // We can't find the resource that you asked for.
                // Handle the exception here, and/or rethrow as needed.
            }
            clog($e);
            clog("DB error", $error);
            return false;
        }
        catch ( \Exception $e )
        {
            clog($e);
            clog("General error", $e);
            return false;
        }

        // Decrypts secret using the associated KMS CMK.
        // Depending on whether the secret is a string or binary, one of these fields will be populated.
        if ( isset($result['SecretString']) )
        {
            $secret = $result['SecretString'];
        }
        else
        {
            $secret = base64_decode($result['SecretBinary']);
        }

        // Your code goes here;
        if ( self::DEBUG_CREDS_DANGEROUS ) clog("secrets - manager secret", $secret);

        return FJ::jsDecode($secret);
    }
}
