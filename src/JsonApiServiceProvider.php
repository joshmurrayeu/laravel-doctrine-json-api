<?php

namespace JMWD\JsonApi;

use Doctrine\Persistence\ManagerRegistry;
use Illuminate\Support\ServiceProvider;
use JMWD\JsonApi\Database\Adapter;
use JMWD\JsonApi\Response\Schema;
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
            __DIR__ . '/../config/json-api.php' => config_path('json-api.php'),
        ], 'config');
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->booting(function () {
            $jsonApi = new JsonApi(url('/v1'));
            $managerRegistry = $this->app->get(ManagerRegistry::class);

            $this->registerResourceTypes($jsonApi, $managerRegistry);
            $this->registerExtensions($jsonApi);

            $this->app->bind(JsonApi::class, function () use ($jsonApi) {
                return $jsonApi;
            });
        });
    }

    /**
     * @param JsonApi         $jsonApi
     * @param ManagerRegistry $managerRegistry
     *
     * @return void
     */
    protected function registerResourceTypes(JsonApi $jsonApi, ManagerRegistry $managerRegistry): void
    {
        $resourceTypes = config('json-api.resourceTypes');

        foreach ($resourceTypes as $resourceType => $utilities) {
            $schemaName = reset($utilities);
            $adapterName = key($utilities);

            /** @var Adapter $adapter */
            $adapter = new $adapterName($managerRegistry);

            /** @var Schema $schema */
            $schema = new $schemaName();

            $jsonApi->resourceType($resourceType, $adapter, $schema->toClosure());
        }
    }

    /**
     * @param JsonApi $jsonApi
     *
     * @return void
     */
    protected function registerExtensions(JsonApi $jsonApi): void
    {
        $extensions = config('json-api.extensions');

        foreach ($extensions as $extension => $options) {
            if (is_numeric($extension)) {
                $extension = $options;
                $options = [];
            }

            $jsonApi->extension(new $extension($options));
        }
    }
}