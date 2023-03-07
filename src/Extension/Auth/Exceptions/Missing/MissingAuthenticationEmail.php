<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions\Missing;

class MissingAuthenticationEmail extends MissingAuthenticationException
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Missing `email` property';
}
