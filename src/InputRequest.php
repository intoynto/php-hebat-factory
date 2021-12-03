<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\Route;
use Slim\Interfaces\RouteInterface;

class InputRequest {
    /**
     * @var array
     */
    protected array $_meta;

    /**
     * @var array
     */
    protected array $_attributes;


    /**
     * @var RouteInterface
     */
    protected $_route;


    /**
     * Constructor
     * @param Request $request
     * @param Route $route
     */
    public function __construct(Request $request, Route $route)
    {
        $this->_route=$route;
        
        $this->_meta=[
            'name'=>$route->getName(),
            'group'=>$route->getGroups(),
            'methods'=>$route->getMethods(),
            'arguments'=>$route->getArguments(),
            'currentUri'=>$request->getUri(),
        ];

        $this->_attributes=$request->getParsedBody()??[];
    }

    public function all(){ return $this->_attributes; }

    public function __set($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->_attributes[$name])?$this->_attributes[$name]:null;
    }

    public function __invoke($name)
    {
        return data_get($this->_attributes,$name);
    }

    /**
     * @param array|string|mixed $columns
     */
    public function forget($columns):self
    {
        $columns=is_array($columns)?$columns:func_get_args();        
        foreach(array_values($columns) as $key)
        {
            if(!\is_null($key))
            {
                unset($this->_attributes[$key]);
            }
        }
        return $this;
    }

    public function merge(array $array):self
    {
        array_walk($array,fn($value,$key)=>data_set($this->_attributes,$key,$value));
        return $this;
    }

    public function fill(array $array):self
    {
        array_walk($array,fn($value,$key)=>data_fill($this->_attributes,$key,$value));
        return $this;
    }

    public function getCurrentUri()
    {
        return data_get($this->_meta,'currentUri');
    }

    public function getName()
    {
        return data_get($this->_meta,'name');
    }

    public function getGroups()
    {
        return data_get($this->_meta,'groups');
    }

    public function getMethods()
    {
        return data_get($this->_meta,'methods');
    }

    public function getArguments()
    {
        return data_get($this->_meta,'arguments');
    }

    /**
     * @return RouteInterface
     */
    public function getRoute()
    {
        return $this->_route;
    }
}