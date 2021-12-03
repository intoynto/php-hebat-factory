<?php

namespace Intoy\HebatFactory;


abstract class Provider 
{
    /**
     * App
     * @var App
     */
    protected $app;

    /**
     * Kernel
     * @var Kernel
     */
    protected $kernel;


    final public function __construct(App $app, Kernel $kernel)
    {
        $this->app=$app;
        $this->kernel=$kernel;
        $this->afterCreate();
    }

    protected function afterCreate(){}

    protected function bind(string $name,callable $resolvable)
    {
        $this->app->getContainer()->set($name,$resolvable);
    }

    protected function resolve(string $name)
    {
        return $this->app->getContainer()->get($name);
    }


    protected function register(){}
    protected function boot(){}



    /**
     * Constructing provider
     * 
     * @param App $app
     * @param Kernel $kernel
     * @param string[] $nameSpaceProviders
     */
    final public static function setup(App $app, Kernel $kernel, array $nameSpaceProviders)
    {
        $run_when_exists=function($provider, string $method)
        {
            if(method_exists($provider,$method))
            {
                $provider->$method();
            }            
        };


        $providers=array_map(function($class) use ($app,$kernel){ return new $class($app,$kernel); },$nameSpaceProviders);

        foreach($providers as $p)
        {
            $run_when_exists($p,'register');
        }

        foreach($providers as $p)
        {
            $run_when_exists($p,'boot');
        }


        foreach($providers as $p)
        {
            $run_when_exists($p,'afterBoot');
        }
    }
}