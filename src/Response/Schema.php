<?php

namespace JMWD\JsonApi\Response;

use Closure;
use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Schema\Type;

abstract class Schema
{
    /**
     * @var class-string $validationClass
     */
    protected string $validationClass;

    /**
     * @param Type $type
     *
     * @return void
     */
    abstract public function schema(Type $type): void;

    /**
     * @return Closure
     */
    public function toClosure(): Closure
    {
        return function (Type $type): void {
            $this->schema($type);
            $this->applyValidation($type);
        };
    }

    /**
     * @param Type $type
     *
     * @return void
     */
    public function applyValidation(Type $type): void
    {
        $fields = $type->getFields();

        if ($this->hasValidationClass() === false) {
            return;
        }

        $validationClass = $this->getValidationClass();

        $methodsOnValidationObject = get_class_methods($validationClass);

        foreach ($fields as $field) {
            $name = $field->getName();

            if (in_array($name, $methodsOnValidationObject)) {
                $field->validate(
                    // @phpstan-ignore-next-line
                    call_user_func("{$validationClass}::{$name}")
                );
            }
        }
    }

    /**
     * @return Closure
     */
    public function authentication(): Closure
    {
        return function (Context $context) {
            // figure out if the user is logged in
            return true;
        };
    }

    /**
     * @return string
     */
    public function getValidationClass(): string
    {
        return $this->validationClass;
    }

    /**
     * @return bool
     */
    public function hasValidationClass(): bool
    {
        return !empty($this->validationClass);
    }
}
