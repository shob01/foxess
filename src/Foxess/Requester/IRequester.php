<?php declare(strict_types=1);

namespace Foxess\Requester;

use \Psr\Http\Message\ResponseInterface;

interface IRequester
{
    public function request(string $method,
                            string $path, 
                            array $headers,
                            string $payload) : ResponseInterface;
}

