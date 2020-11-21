<?php



namespace FJ;



use Aws\S3\S3Client;
use Exception;



class AuthProviderAWS extends AuthProviderBase
{
    /**
     * @var S3Client
     */
    private $s3     = false;
    private $bucket = false;
    private $key    = false;



    function __construct ( $params )
    {
        $this->s3     = $params['s3'];
        $this->bucket = $params['auth_bucket'];
        $this->key    = $params['auth_key'];
    }



    /**
     * @throws Exception
     */
    public function init ()
    {
        if ( false === $this->s3 )
        {
            redlog("AuthP.init(): Cannot establish AWS S3 connection.");
            throw new Exception("Cannot get AWS S3 reference; aborting.");
        }

        clog("AuthP.init()", "Auth (cloud) successfully init'ed.");
    }



    public function loadAuthData ( $user )
    {
        $authJSON = $this->getFromS3();
        $auth     = FJ::jsDecode($authJSON);

        return array_key_exists($user, $auth)
            ? $auth[$user]
            : false;
    }



    private function getFromS3 ()
    {
        $params = [
            'Bucket' => $this->bucket,
            'Key'    => $this->key,
        ];

        return $this->getRawFromS3($params);
    }



    private function getRawFromS3 ( $params )
    {
        $result = $this->s3->getObject($params);
        $raw    = $result['Body'];

        return $raw;
    }
}
