<?php

namespace JMWD\JsonApi\Http\Concerns;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tobyz\JsonApiServer\JsonApi;

trait CanDefault
{
    /**
     * @param JsonApi                $api
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function default(JsonApi $api, ServerRequestInterface $request): ResponseInterface
    {
        try {
            $response = $api->handle($request);
        } catch (Exception $e) {
            $response = $api->error($e);

            if (env('APP_DEBUG') === true && $response->getStatusCode() === 500) {
                dd($e);
            }
        }

        return $response;
    }
}