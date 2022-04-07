<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Nyholm\Psr7\Response as NyholmResponse;

class Response extends NyholmResponse
{
    public function withJson($value):self
    {
        $this->getBody()->write(json_encode($value));
        return $this->withHeader('content-type','application/json');
    }
}