<?php

namespace JMWD\JsonApi;

use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;
use JMWD\JsonApi\Database\Adapter;
use JMWD\JsonApi\Response\Schema;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tobyz\JsonApiServer\JsonApi;

/**
 * Class JsonApiServiceProvider.
 */
class JsonApiServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/json-api.php' => config_path('dpd.php'),
        ], 'config');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return void
     */
    public function register(): void
    {
        $jsonApi = new JsonApi(url('/v1'));
        $entityManager = $this->app->get(EntityManager::class);

        $this->registerResourceTypes($jsonApi, $entityManager);

        $this->app->bind(JsonApi::class, function () use ($jsonApi) {
            return $jsonApi;
        });
    }

    /**
     * @param JsonApi       $jsonApi
     * @param EntityManager $entityManager
     *
     * @return void
     */
    protected function registerResourceTypes(JsonApi $jsonApi, EntityManager $entityManager): void
    {
        $resourceTypes = config('json-api.resourceTypes');

        foreach ($resourceTypes as $resourceType => $utilities) {
            $schemaName = reset($utilities);
            $adapterName = key($utilities);

            /** @var Adapter $adapter */
            $adapter = new $adapterName($entityManager);

            /** @var Schema $schema */
            $schema = new $schemaName();

            $jsonApi->resourceType($resourceType, $adapter, $schema->toClosure());
        }
    }
}