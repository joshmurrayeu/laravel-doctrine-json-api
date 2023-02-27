<?php

namespace JMWD\JsonApi\Error\Internal;

use JMWD\JsonApi\Error\Exception;

class UndefinedRelationshipType extends Exception
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Undefined relationship type.';

    /**
     * @var string $message
     */
    protected $message = 'Undefined relationship type.';
}
