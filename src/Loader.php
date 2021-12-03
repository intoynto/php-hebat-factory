<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;


class Loader
{
    /**
     * App
     * @var App
     */
    public $app;

    /**
     * Kernel
     * @var Kernel
     */
    public $kernel;

    /**
     * Prefix
     * @var string 
     */
    public string $prefix='';

    final public function __construct(App &$app, Kernel &$kernel)
    {
        $this->app=$app;
        $this->kernel=$kernel;
    }    

    protected function beforeBoot(){}
    protected function boot(){}
    protected function afterBoot(){}

    /**
     * call constructor loader
     */
    final public static function setup(App &$app, Kernel &$kernel, array $nameSpaceLoaders)
    {
        $loaders=array_map(function($class) use ($app,$kernel){
            return new $class($app,$kernel);
        },array_values($nameSpaceLoaders));

        foreach($loaders as $l)
        {
            $l->beforeBoot();
        }

        foreach($loaders as $l)
        {
            $l->boot();
        }
        
        foreach($loaders as $l){
            $l->afterBoot();
        }
    }
}