<?php

namespace JMWD\JsonApi\Http\Concerns;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobyz\JsonApiServer\JsonApi;

trait CanList
{
    use CanDefault;

    /**
     * @param JsonApi                $api
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function list(JsonApi $api, ServerRequestInterface $request): ResponseInterface
    {
        return $this->default($api, $request);
    }
}