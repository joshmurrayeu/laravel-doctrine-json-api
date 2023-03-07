<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions\Missing;

class MissingAuthenticationCredentials extends MissingAuthenticationException
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Missing `email` and `password` properties';
}
