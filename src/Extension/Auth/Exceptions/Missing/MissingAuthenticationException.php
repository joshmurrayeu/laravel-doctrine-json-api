<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions\Missing;

use JMWD\JsonApi\Error\Exception;

abstract class MissingAuthenticationException extends Exception
{
    /**
     * @inheritDoc
     */
    protected $message = "Please send the authentication request in JSON:API format.";

    /**
     * @inheritDoc
     */
    public function getJsonApiStatus(): string
    {
        return '422';
    }
}
