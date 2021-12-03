<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Http\Message\ServerRequestInterface as Request;

class Context {
    /**
     * @var Request
     */
    private $request;

    public function storeRequest(Request $request)
    {
        $this->request=$request;        
    }

    public function getRequest():Request
    {
        return $this->request;        
    }
    /**
     * @return string
     */
    public function getBasePath()
    {
        $base="";
        if($this->request)        
        {
            $base=static::resolveBasePathFromScriptName($this->request->getServerParams());
            $base=$base?rtrim($base,"/"):$base;
        }
        return $base;
    }

    /**
     * @var array $server from global from var $_SERVER atau ServerRequestInterface->getServerParams()
     * @return string
     */
    public static function resolveBasePathFromScriptName(array $server)
    {
        $scriptName = $server['SCRIPT_NAME'];
        $basePath = str_replace('\\', '/', dirname($scriptName));

        if (strlen($basePath) > 1) {
            return $basePath;
        }
        return '';
    }

    /**
     * @var array $server from global from var $_SERVER atau ServerRequestInterface->getServerParams()
     * @return string
     */
    public static function resolveBasepathFromRequestUri(array $server)
    {
        if (!isset($server['REQUEST_URI'])) {
            return '';
        }

        $scriptName = $server['SCRIPT_NAME'];

        $basePath = (string)parse_url($server['REQUEST_URI'], PHP_URL_PATH);
        $scriptName = str_replace('\\', '/', dirname(dirname($scriptName)));

        if ($scriptName === '/') {
            return '';
        }

        $length = strlen($scriptName);
        if ($length > 0 && $scriptName !== '/') {
            $basePath = substr($basePath, 0, $length);
        }

        if (strlen($basePath) > 1) {
            return $basePath;
        }

        return '';
    }

    /**
     * @var array $server from global from var $_SERVER atau ServerRequestInterface->getServerParams()
     * @return string
     */
    public static function resolveBasepathRelativeScriptName(array $server)
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            return $_SERVER['SCRIPT_NAME'];
        }
        if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
            return $scriptDir;
        }
        return '';
    }
}