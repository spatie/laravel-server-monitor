<?php

namespace Spatie\ServerMonitor\Test;

use GuzzleHttp\Client;

class Server
{
    /** @var \GuzzleHttp\Client */
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function setResponse(string $listenFor, string $response)
    {
        $this->client->post('http://localhost:8080/setServerResponse', [
            'form_params' => compact('listenFor', 'response'),
        ]);
    }
}
