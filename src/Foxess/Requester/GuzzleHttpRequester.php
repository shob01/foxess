<?php

declare(strict_types=1);

namespace Foxess\Requester;

use \Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;


class GuzzleHttpRequester implements IRequester
{
    public function request(string $method,string $path,array $headers,string $payload): ResponseInterface 
    {
        $client = new Client();
        $response = $client->request($method, $path, [
            'headers' => $headers,
            'body' => $payload
        ]);

        return $response;
    }
}
