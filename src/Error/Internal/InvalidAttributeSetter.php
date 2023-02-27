<?php

namespace JMWD\JsonApi\Error\Internal;

use JMWD\JsonApi\Error\Exception;

class InvalidAttributeSetter extends Exception
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Invalid attribute setter';

    /**
     * @inheritDoc
     *
     * @phpstan-ignore-next-line
     */
    protected $message = "%s() doesn't exist on %s.";

    /**
     * @param string $method
     * @param string $entity
     */
    public function __construct(string $method, string $entity)
    {
        $message = sprintf($this->message, $method, $entity);

        parent::__construct($message);
    }
}
