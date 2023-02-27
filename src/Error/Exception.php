<?php

namespace JMWD\JsonApi\Error;

use JsonApiPhp\JsonApi\Error;
use RuntimeException;
use Tobyz\JsonApiServer\ErrorProviderInterface;

abstract class Exception extends RuntimeException implements ErrorProviderInterface
{
    /**
     * @var string $title
     */
    protected string $title;

    /**
     * @inheritDoc
     */
    public function getJsonApiErrors(): array
    {
        return [
            new Error(
                new Error\Title($this->getTitle()),
                new Error\Status($this->getJsonApiStatus()),
                new Error\Detail($this->getMessage())
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function getJsonApiStatus(): string
    {
        return '500';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
