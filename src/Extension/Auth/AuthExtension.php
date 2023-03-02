<?php

namespace JMWD\JsonApi\Extension\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use JMWD\JsonApi\Extension\Auth\Contracts\JWTSubject;
use JMWD\JsonApi\Extension\Auth\Exceptions\Missing\MissingAuthenticationAttributes;
use JMWD\JsonApi\Extension\Auth\Exceptions\Missing\MissingAuthenticationCredentials;
use JMWD\JsonApi\Extension\Auth\Exceptions\Missing\MissingAuthenticationData;
use JMWD\JsonApi\Extension\Auth\Exceptions\Missing\MissingAuthenticationEmail;
use JMWD\JsonApi\Extension\Auth\Exceptions\Missing\MissingAuthenticationPassword;
use JMWD\JsonApi\Extension\Auth\Exceptions\InvalidCredentials;
use JMWD\JsonApi\Extension\Auth\Exceptions\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Endpoint\Concerns\FindsResources;
use Tobyz\JsonApiServer\Endpoint\Show;
use Tobyz\JsonApiServer\Extension\Extension;
use Tymon\JWTAuth\JWTGuard;

class AuthExtension extends Extension
{
    use FindsResources;

    /**
     * @param array $options
     */
    public function __construct(
        protected array $options = []
    ) {
        //
    }

    /**
     * @return string
     */
    public function uri(): string
    {
        return 'https://jsonapi.jmwd.tech/extensions/auth';
    }

    /**
     * @param Context $context
     *
     * @return ResponseInterface|null
     */
    public function handle(Context $context): ?ResponseInterface
    {
        if ($context->getPath() === '/users/login') {
            return $this->login($context);
        }

        if ($context->getPath() === '/users/whoami') {
            return $this->whoami($context);
        }

        return null;
    }

    /**
     * @param Context $context
     *
     * @return ResponseInterface
     */
    protected function login(Context $context): ResponseInterface
    {
        $request = collect($context->getRequest()->getParsedBody());

        if (($data = collect($request->get('data'))) && $data->isEmpty()) {
            throw new MissingAuthenticationData();
        }

        if (($attributes = collect($data->get('attributes'))) && $attributes->isEmpty()) {
            throw new MissingAuthenticationAttributes();
        }

        $hasEmail = $attributes->has('email');
        $hasPassword = $attributes->has('password');

        if ($hasEmail === false) {
            if ($hasPassword === false) {
                throw new MissingAuthenticationCredentials();
            }

            throw new MissingAuthenticationEmail();
        } elseif ($hasPassword === false) {
            throw new MissingAuthenticationPassword();
        }

        $credentials = $attributes->only(['email', 'password'])->toArray();

        $jwt = $this->getAuthGuard()->attempt($credentials);

        if (empty($jwt)) {
            throw new InvalidCredentials();
        }

        return $this->userAsJsonApiObject($context, $jwt);
    }

    /**
     * @param Context $context
     *
     * @return ResponseInterface
     */
    protected function whoami(Context $context): ResponseInterface
    {
        $request = $context->getRequest();
        $authorization = $request->getHeaderLine('Authorization');
        $authGuard = $this->getAuthGuard();

        if (empty($authorization) || $authGuard->check() === false) {
            throw new UnauthorizedException();
        }

        return $this->userAsJsonApiObject($context);
    }

    /**
     * @param Context     $context
     * @param string|null $jwt
     *
     * @return ResponseInterface
     */
    protected function userAsJsonApiObject(Context $context, ?string $jwt = null): ResponseInterface
    {
        $resourceType = $context->getApi()->getResourceType('users');

        /** @var JWTSubject $entity */
        $entity = $this->getAuthGuard()->user();

        $entity->setJWT($jwt);

        return (new Show())->handle($context, $resourceType, $entity);
    }

    /**
     * @return Guard|JWTGuard
     */
    private function getAuthGuard(): Guard|JWTGuard
    {
        return Auth::guard();
    }
}