<?php

namespace JMWD\JsonApi\Extension\Auth\Exceptions\Missing;

class MissingAuthenticationData extends MissingAuthenticationException
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Missing `data` object';
}
