<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteResolverInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Factory\AppFactory as SlimAppFactory;
use Intoy\HebatFactory\App;

class AppFactory extends SlimAppFactory 
{
    /**
     * @var App
     */
    public static App $app;    

    /**
     * @param ResponseFactoryInterface|null         $responseFactory
     * @param ContainerInterface|null               $container
     * @param CallableResolverInterface|null        $callableResolver
     * @param RouteCollectorInterface|null          $routeCollector
     * @param RouteResolverInterface|null           $routeResolver
     * @param MiddlewareDispatcherInterface|null    $middlewareDispatcher
     * @return App
     */
    public static function create(
        ?ResponseFactoryInterface $responseFactory = null, 
        ?ContainerInterface $container = null, 
        ?CallableResolverInterface $callableResolver = null, 
        ?RouteCollectorInterface $routeCollector = null, 
        ?RouteResolverInterface $routeResolver = null, 
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null): App
    {
        static::$responseFactory = $responseFactory ?? static::$responseFactory;
        static::$app=new App(
            self::determineResponseFactory(),
            $container ?? static::$container,
            $callableResolver ?? static::$callableResolver,
            $routeCollector ?? static::$routeCollector,
            $routeResolver ?? static::$routeResolver,
            $middlewareDispatcher ?? static::$middlewareDispatcher
        );
        return static::$app;
    }

    /**
     * @param ContainerInterface $container
     * @return App
     */
    public static function createFromContainer(ContainerInterface $container): App
    {
        $responseFactory = $container->has(ResponseFactoryInterface::class)
            ? $container->get(ResponseFactoryInterface::class)
            : self::determineResponseFactory();

        $callableResolver = $container->has(CallableResolverInterface::class)
            ? $container->get(CallableResolverInterface::class)
            : null;

        $routeCollector = $container->has(RouteCollectorInterface::class)
            ? $container->get(RouteCollectorInterface::class)
            : null;

        $routeResolver = $container->has(RouteResolverInterface::class)
            ? $container->get(RouteResolverInterface::class)
            : null;

        $middlewareDispatcher = $container->has(MiddlewareDispatcherInterface::class)
            ? $container->get(MiddlewareDispatcherInterface::class)
            : null;

        static::$app=new App(
            $responseFactory,
            $container,
            $callableResolver,
            $routeCollector,
            $routeResolver,
            $middlewareDispatcher
        );
        $app=static::$app;
        $class=get_class($app);
        if(method_exists($container,'set'))
        {
            \call_user_func_array([$container,'set'],[$class,$app]);
        }
        return static::$app;
    }
}