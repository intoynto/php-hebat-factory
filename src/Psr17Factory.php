<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory as NyholmPsr17Factory;
use Slim\Interfaces\ServerRequestCreatorInterface;
use Slim\Factory\Psr17\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreator as NyholmServerRequestCreator;

use Intoy\HebatFactory\Request;
use Intoy\HebatFactory\Response;

class Psr17Factory extends NyholmPsr17Factory
{
    public static function getServerRequestCreator():ServerRequestCreatorInterface
    {
        $psr17Factory=new Psr17Factory();
        $serverRequestCreator=new NyholmServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
        );
        return new ServerRequestCreator($serverRequestCreator,'fromGlobals'); 
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new Request($method, $uri, [], null, '1.1', $serverParams);
    }


    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if (2 > \func_num_args()) {
            // This will make the Response class to use a custom reasonPhrase
            $reasonPhrase = null;
        }

        return new Response($code, [], null, '1.1', $reasonPhrase);
    }
}