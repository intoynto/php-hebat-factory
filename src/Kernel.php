<?php

declare(strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Middleware\{ErrorMiddleware as SlimErrorMiddleware};
use Intoy\HebatFactory\Handlers\ShutdownHandler;

abstract class Kernel
{

    /**
     * App
     * @var App
     */
    protected $app;

    /**
     * Global loader
     * @var string[]
     */
    protected $loaders=[];

    /**
     * GLOBAL Middleware
     * Middleware proses di mulai dari bawah ke atas
     * Oleh karena itu RouteProvider akan mereverse ulang array dari bawah ke atas
     * @var array
     */
    public array $middleware=[];

    /**
     * Route Group Middleware
     * Middleware proses di mulai dari bawah ke atas
     * Oleh karena itu RouteProvider harus mereverse ulang array dari bawah ke atas
     * @var array
     */
    public array $middlewareGroups=[
        'api'=>[],
        'web'=>[],
    ];
    
    /**
     * @var array
     */
    protected $takes;


    /**
     * @var SlimErrorMiddleware
     */
    protected $errorMiddleware;


    public function __construct(App $app)
    {
        $this->app=$app;
        $this->app->bind(get_class($this),$this); // register to container by string class
        $this->app->bind('app.kernel',$this); // register to container by alias string
    }

    protected function getApp():App
    {
        return $this->app;
    }

    protected function getKernel():Kernel
    {
        return $this;
    }

    /**
     * @return array
     */
    protected function getLoaders(string $prefix)
    {
        return [...$this->loaders,...$this->groupLoaders[$prefix]];
    }


    protected function onFinishSetup()
    {
    }


    public function registerShutdownHandler(Request $request)
    {
        return;
        if($this->errorMiddleware)
        {
            $errorHandle=$this->errorMiddleware->getDefaultErrorHandler();
            if($errorHandle instanceof \Slim\Handlers\ErrorHandler)
            {
                $shutdownHandler=new ShutdownHandler($request, $errorHandle,!is_production());
                register_shutdown_function($shutdownHandler);
            }
        }
    }
    

    public function setup()
    {
        $app=$this->getApp();
        $kernel=$this->getKernel();
        $nameSpaceLoaders=$this->loaders;
        Loader::setup($app,$kernel,$nameSpaceLoaders);

        $this->onFinishSetup();
    }
}