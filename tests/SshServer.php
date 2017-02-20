<?php

namespace Spatie\ServerMonitor\Test;

class SshServer
{
    public static function setResponse(string $listenFor, string $response)
    {
        $fileContents = json_encode([
            'expect' => $listenFor,
            'output' => $response,
        ]);

        file_put_contents(__DIR__.'/server/store.json', $fileContents);
    }
}
