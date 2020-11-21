<?php



namespace FJ;



use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;



class FileProviderAWS extends FileProviderBase
{
    /**
     * @var S3Client
     */
    private $s3     = false;
    private $bucket = false;



    function __construct ( $params )
    {
        $this->s3     = $params['s3'];
        $this->bucket = $params['file_bucket'];
    }



    /**
     * @param $params array|bool
     *
     * @throws Exception
     */
    public function init ( $params = false )
    {
        if ( false === $this->s3 )
        {
            redlog("FP.init(): Cannot establish AWS S3 connection.");
            throw new Exception("Cannot get AWS S3 reference; aborting.");
        }

        if ( false !== $params )
        {
            if ( array_key_exists("bucket", $params) )
            {
                $this->bucket = $params["bucket"];
            }
        }

        clog("FP.init()", "S3Client successfully init'ed.");
    }



    public function ls ( $params = false )
    {
        $prefix = (false === $params) ? false : $params['prefix'];

        if ( self::DEBUG_S3_VERBOSE ) clog("ante-iterator");

        $params = [
            'Bucket' => $this->bucket,
        ];

        if ( false !== $prefix ) $params['Prefix'] = $prefix;

        $objects = $this->s3->getIterator('ListObjects', $params);

        if ( self::DEBUG_S3_VERBOSE ) clog("post-iterator");

        $list = [];

        foreach ( $objects as $object )
        {
            $key    = $object['Key'];
            $list[] = $key;
        }

        if ( self::DEBUG_S3 ) clog("S3 object count", count($list));

        return $list;
    }



    public function write ( $path, $data, $meta = false )
    {
        $data = trim($data) . "\n";

        $params = [
            'Bucket' => $this->bucket,
            'Key'    => $path,
            'Body'   => $data,
        ];

        if ( false != $meta ) $params['Metadata'] = $meta;

        clog("S3.put", $params);

        try
        {
            $result = $this->s3->putObject($params);

            clog("S3 Object URL", $result['ObjectURL']);
        }
        catch ( S3Exception $e )
        {
            clog($e);
        }
    }



    public function read ( $path )
    {
        $params = [
            'Bucket' => $this->bucket,
            'Key'    => $path,
        ];

        try
        {
            $data = $this->getRawFromS3($params);
        }
        catch ( S3Exception $e )
        {
            clog($e);
            return false;
        }

        return trim($data);
    }



    private function getRawFromS3 ( $params )
    {
        $result = $this->s3->getObject($params);
        $raw    = $result['Body'];

        return $raw;
    }
}
