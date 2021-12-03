<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use DI\Container;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App as SlimApp;
use Intoy\HebatFactory\Psr17Factory;

class App extends SlimApp 
{
    public function getKernel():Kernel
    {
        return $this->container->get('app.kernel');
    }
    
    public function getContainer(): Container
    {
        return $this->container;        
    }

    public function call(...$arguments)
    {
        return $this->getContainer()->call(...$arguments);
    }

    public function has(...$arguments)
    {
        return $this->getContainer()->has(...$arguments);
    }

    public function bind(...$arguments)
    {
        return $this->getContainer()->set(...$arguments);
    }

    public function make(...$arguments)
    {
        return $this->getContainer()->make(...$arguments);
    }

    public function resolve(...$arguments)
    {
        return $this->getContainer()->get(...$arguments);
    }


    /**
     * Run application
     *
     * This method traverses the application middleware stack and then sends the
     * resultant Response object to the HTTP client.
     *
     * @param ServerRequestInterface|null $request
     * @return void
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        if(!$request)
        {
            $request=$this->has(ServerRequestInterface::class);
            if(!$request)
            {
                ///create request decorator request
                $request=Psr17Factory::getServerRequestCreator()->createServerRequestFromGlobals();
            }
        }

        //register shutdown handle in kernel
        $this->getKernel()->registerShutdownHandler($request); 

        // call parent run request 
        parent::run($request);
    }    
}