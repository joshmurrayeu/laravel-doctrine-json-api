<?php

namespace JMWD\JsonApi\Error\Internal;

use JMWD\JsonApi\Error\Exception;

class MethodNotDefined extends Exception
{
    /**
     * @inheritDoc
     */
    protected string $title = 'Method not defined';

    /**
     * @var string $message
     */
    protected $message = "%s() hasn't been defined yet.";

    /**
     * @param string $method
     */
    public function __construct(string $method)
    {
        $message = sprintf($this->message, $method);

        parent::__construct($message);
    }
}
