<?php

namespace Foxess\TokenStore;

interface ITokenStore
{
    public function get() : string;
    public function store(string $token) : void;
}

?>