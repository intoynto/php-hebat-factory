<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Log\LoggerInterface;
use App\Factory\Renderer\{HtmlErrorRenderer,JsonErrorRenderer};

use App\Factory\Loaders\{
    LoaderSession,
    LoaderConfig,
    LoaderDatabase,
    LoaderLogger,
    LoaderMiddleware,
    LoaderProvider,
    LoaderView,
};

use App\JWTMiddleware\JWTMiddleware;

use App\Middleware\{
    SessionMiddleware,
    BasePathMiddleware,
    GuardMiddleware,
    TrailingSlashMiddleware,
    CorsMiddleware,
    RouteContextMiddleware,

    TwigHelperMiddleware,
};

use Slim\Middleware\{BodyParsingMiddleware as SlimBodyParsingMiddleware,ErrorMiddleware as SlimErrorMiddleware};

class HttpKernel extends Kernel
{
    /**
     * Global loader
     * @var string[]
     */
    public $loaders=[
        LoaderSession::class,
        LoaderConfig::class,
        LoaderDatabase::class,
        LoaderLogger::class,
        LoaderView::class,
        LoaderMiddleware::class,
        LoaderProvider::class,
    ];

    /**
     * {@inheritdoc}
     */
    public array $middleware=[
        SessionMiddleware::class, //start first for session  
        TrailingSlashMiddleware::class, // redirect trailing slash
        CorsMiddleware::class,
        SlimBodyParsingMiddleware::class,
        RouteContextMiddleware::class, //register tracking input
    ];
    
    /**
     * {@inheritdoc}
     */
    public array $middlewareGroups=[
        'web'=>[
            GuardMiddleware::class,            
            \Slim\Views\TwigMiddleware::class, //default slim Twig middleware runtime extension
            TwigHelperMiddleware::class, // global var and Wbpack Extension  
        ],
        'api'=>[
           JWTMiddleware::class,
        ],
    ];


    /**
     * @return LoggerInterface|null
     */
    protected function resolveLogger()
    {
        $verbs=[
            LoggerInterface::class,
            "logger.app",
            "logger.web",
            "logger.api"
        ];
        $logger=null;
        foreach($verbs as $log)
        {
            if(app()->has($log))
            {
                $logger=app()->resolve($log);
                break;
            }
        }
        return $logger;
    }


    protected function resolveErrorMilddleware():SlimErrorMiddleware
    {
        $mid=app()->addErrorMiddleware(!is_production(),true,true);
        return $mid;
    }


    protected function onFinishSetup()
    {
        $mid=$this->resolveErrorMilddleware();
        $errorHandle=$mid->getDefaultErrorHandler();
        if($errorHandle instanceof \Slim\Handlers\ErrorHandler)
        {
            $errorHandle->registerErrorRenderer("text/html",HtmlErrorRenderer::class);
            $errorHandle->registerErrorRenderer("application/json",JsonErrorRenderer::class);
            $errorHandle->registerErrorRenderer("text/json",JsonErrorRenderer::class);
        }
        app()->add(BasePathMiddleware::class);
    }
}