<?php

namespace JMWD\JsonApi\Validation;

use App\Doctrine\AbstractRepository;
use Doctrine\ORM\EntityRepository;

abstract class Validator
{
    /**
     * @var class-string
     */
    protected static string $repositoryClass = EntityRepository::class;

    /**
     * @return AbstractRepository
     */
    protected static function getRepository(): AbstractRepository
    {
        return app(static::getRepositoryClass());
    }

    /**
     * @return string
     */
    public static function getRepositoryClass(): string
    {
        return static::$repositoryClass;
    }
}
