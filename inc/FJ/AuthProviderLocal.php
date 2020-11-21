<?php



namespace FJ;



use Exception;



class AuthProviderLocal extends AuthProviderBase
{
    private $authFilePath;



    function __construct ( $params )
    {
        $this->authFilePath = $params["auth_file"];
    }



    /**
     * @throws Exception
     */
    public function init ()
    {
        $canRead = file_exists($this->authFilePath) && is_readable($this->authFilePath);

        if ( false === $canRead )
        {
            redlog("AuthP.init(): Cannot open auth file [ " . $this->authFilePath . " ].");
            throw new Exception("Cannot open auth file.");
        }

        clog("AuthP.init()", "Auth (local) successfully init'ed.");
    }



    public function loadAuthData ( $user )
    {
        clog("AUTH - local", $this->authFilePath);

        $json        = file_get_contents($this->authFilePath);
        $allUserData = FJ::jsDecode($json);

        if ( array_key_exists($user, $allUserData) )
        {
            $userData = $allUserData[$user];
        }
        else
        {
            redlog("loadAuthData - User [ $user ] not in system.");
            $userData = false;
        }

        return $userData;
    }
}
