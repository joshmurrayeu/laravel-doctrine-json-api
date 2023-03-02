<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions\Missing;

class MissingAuthenticationPassword extends MissingAuthenticationException
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Missing `password` property';
}
