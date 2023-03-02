<?php

namespace JMWD\JsonApi\Extension\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use JMWD\JsonApi\Extension\Auth\Exceptions\InvalidCredentials;
use JMWD\JsonApi\Extension\Auth\Exceptions\UnauthorizedException;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Tobyz\JsonApiServer\Context;
use Tobyz\JsonApiServer\Endpoint\Concerns\FindsResources;
use Tobyz\JsonApiServer\Endpoint\Show;
use Tobyz\JsonApiServer\Extension\Extension;
use Tymon\JWTAuth\JWTGuard;

use function Tobyz\JsonApiServer\json_api_response;

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
     * @return Response
     */
    protected function login(Context $context): Response
    {
        $request = collect($context->getRequest()->getParsedBody());
        $credentials = $request->only(['email', 'password'])->toArray();

        $jwt = $this->getAuthGuard()->attempt($credentials);

        if (empty($jwt)) {
            throw new InvalidCredentials();
        }

        return json_api_response([
            'auth-extension:response' => [
                'jwt' => $jwt,
            ],
        ]);
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

        $resourceType = $context->getApi()->getResourceType('users');
        $entity = $authGuard->user();

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