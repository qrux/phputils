<?php



namespace FJ;



use Aws\S3\S3Client;
use FJ\FJ;
use FJ\Log;
use function FJ\cclog;
use function FJ\clog;
use function FJ\cynulog;
use function FJ\redlog;
use function FJ\redulog;



interface AuthProvider
{
    const USER_LOGIN_KEY = 'user';
    const USER_PASS_KEY  = 'pass';
    const USER_HASH_KEY  = 'hash';
    const USER_NAME_KEY  = 'name';
    const USER_PIC_KEY   = 'pic';
    const USER_ROLE_KEY  = 'role';


    function init ();
    function loadAuthData ( $user );
    function getRole ( $user );
}
