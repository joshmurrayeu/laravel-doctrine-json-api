<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions;

use JMWD\JsonApi\Error\Exception;

class UnauthorizedException extends Exception
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Unauthorised';

    /**
     * @inheritDoc
     */
    protected $message = "Please try again with another Bearer token.";

    /**
     * @inheritDoc
     */
    public function getJsonApiStatus(): string
    {
        return '403';
    }

}
