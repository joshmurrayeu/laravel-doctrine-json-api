<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions;

use JMWD\JsonApi\Error\Exception;

class InvalidCredentials extends Exception
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Invalid login credentials';

    /**
     * @inheritDoc
     */
    protected $message = "Please try again with new credentials.";

    /**
     * @inheritDoc
     */
    public function getJsonApiStatus(): string
    {
        return '422';
    }

}
