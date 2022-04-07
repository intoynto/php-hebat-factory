<?php

declare(strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Middleware\{ErrorMiddleware as SlimErrorMiddleware};

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

    /**
     * @var bool
     */
    protected $useShutdownHandler=false;


    /**
     * @var array<string|callbale>
     */
    protected $errorRenders=[];


    /**
     * @var array 
     */
    protected $unusedMiddlewares=[];


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

    /**
     * callable yang akan dipanggil 
     * oleh app ketika akan menjalankan request
     */
    public function registerShutdownHandler(Request $request)
    {

    }

    /**
     * method set useShutdownHandler
     * @param bool $value
     */
    public function setUseShutdownHandler(bool $value)
    {
        $this->useShutdownHandler=$value;
    }

    /**
     * Register an error render form a specific content-type
     * 
     * @param string $contextType application/json,application/xml,text/xml,text/html,text/plain
     * @param string|callable $errorRender the error renderer
     */
    public function registerErrorRender(string $contextType,$errorRender)
    {
        $contextType=strtolower($contextType);
        $this->errorRenders[$contextType]=$errorRender;
    }


    public function unsetMiddleware($prefix, $middlewareClass)
    {
        if(!$middlewareClass || !is_string($middlewareClass))
        {
            return;
        }

        $this->unusedMiddlewares[]=[$prefix,$middlewareClass];
    }


    protected function applyUnusedMiddleware()
    {
        foreach($this->unusedMiddlewares as $array)
        {
            $prefix=$array[0];
            $middlewareClass=$array[1];

            if($prefix==="")
            {
                array_splice($this->middleware,array_search($middlewareClass,$this->middleware),1);
            }
            elseif(in_array($prefix,array_keys($this->middlewareGroups)))
            {
                array_splice($this->middlewareGroups[$prefix],array_search($middlewareClass,$this->middlewareGroups[$prefix]),1);
            }
        }

        $after_middleware=$this->middleware;
        $after_groups=$this->middlewareGroups;
    }

    /**
     * Callable if finish setup
     */
    protected function onFinishSetup()
    {
    }
    

    public function setup()
    {
        $app=$this->getApp();
        $kernel=$this->getKernel();

        $this->applyUnusedMiddleware(); //remove unused middleware

        $nameSpaceLoaders=$this->loaders;
        Loader::setup($app,$kernel,$nameSpaceLoaders);

        
        $this->onFinishSetup();
    }
}