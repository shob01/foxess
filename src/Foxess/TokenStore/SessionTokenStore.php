<?php

namespace Foxess\TokenStore;

class SessionTokenStore implements ITokenStore
{
    private const TOKEN_NAME = 'ACCESS-TOKEN';
    
    public function get() : string
    {
        return isset($_SESSION[self::TOKEN_NAME]) ? $_SESSION[self::TOKEN_NAME] : '';
    }
    public function store(string $token) : void
    {
        $_SESSION[self::TOKEN_NAME] = $token; 
    }
}
?>