<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions\Missing;

class MissingAuthenticationAttributes extends MissingAuthenticationException
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Missing `attributes` object';
}
