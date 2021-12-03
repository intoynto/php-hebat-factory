<?php
declare (strict_types=1);

namespace Intoy\HebatFactory;

use Psr\Http\Message\ResponseFactoryInterface as Factory; 
use Psr\Http\Message\ResponseInterface as Response;

class Redirect {
    /**
     * Factory
     * @var Response
     */
    protected $response;

    public function __construct(Factory $factory)
    {
        $this->response=$factory->createResponse(302);
    }

    public function __invoke(string $to)
    {
        return $this->response->withHeader('Location',$to);
    }
}