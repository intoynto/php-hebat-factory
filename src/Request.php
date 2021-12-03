<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Nyholm\Psr7\ServerRequest;

class Request extends ServerRequest
{
    public function isPost():bool
    {
        return strtolower((string)$this->getMethod())==='post';
    }

    public function getParam($name, $default=null)
    {
        $params=$this->isPost()?$this->getParsedBody():$this->getQueryParams();
        if (is_array($params) && false === \array_key_exists($name, $params)) {
            return $default;
        }

        return $params[$name];
    }


    /**
     * Fetch associative array of body and query string parameters.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * @return mixed[]
     */
    public function getParams(): array
    {
        $params = $this->getQueryParams();
        $postParams = $this->getParsedBody();

        if ($postParams) {
            $params = array_merge($params, (array)$postParams);
        }

        return $params;
    }
}