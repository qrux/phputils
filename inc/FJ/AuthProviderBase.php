<?php



namespace FJ;



abstract class AuthProviderBase implements AuthProvider
{
    public function getRole ( $user )
    {
        if ( !array_key_exists(self::USER_ROLE_KEY, $user) )
        {
            if ( !array_key_exists(self::USER_LOGIN_KEY, $user) )
            {
                redulog("No role for invalid user; returning (false)");
                return false;
            }

            $name = $user[self::USER_LOGIN_KEY];
            redulog("No role for user [ $name ]; returning (false)");
            return false;
        }

        return $user[self::USER_ROLE_KEY];
    }
}
