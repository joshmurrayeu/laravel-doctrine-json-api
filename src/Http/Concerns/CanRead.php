<?php

namespace JMWD\JsonApi\Http\Concerns;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobyz\JsonApiServer\JsonApi;

trait CanRead
{
    use CanDefault;

    /**
     * @param JsonApi                $api
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function read(JsonApi $api, ServerRequestInterface $request): ResponseInterface
    {
        return $this->default($api, $request);
    }
}