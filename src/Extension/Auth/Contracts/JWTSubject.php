<?php

namespace JMWD\JsonApi\Extension\Auth\Contracts;

use JMWD\JsonApi\Extension\Auth\AuthExtension;

interface JWTSubject
{
    /**
     * Returns the JWT that was set whilst logging in.
     *
     * @return string|null
     *
     * @see AuthExtension
     */
    public function getJWT(): ?string;

    /**
     * @param string $token
     *
     * @return JWTSubject
     */
    public function setJWT(string $token): JWTSubject;
}
