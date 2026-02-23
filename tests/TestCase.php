<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests;

use Dedoc\Scramble\Generator;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\ScrambleServiceProvider;
use Dedoc\Scramble\Support\OperationBuilder;
use Dedoc\Scramble\Support\OperationExtensions\DeprecationExtension;
use Dedoc\Scramble\Support\OperationExtensions\ErrorResponsesExtension;
use Dedoc\Scramble\Support\OperationExtensions\RequestBodyExtension;
use Dedoc\Scramble\Support\OperationExtensions\RequestEssentialsExtension;
use Dedoc\Scramble\Support\OperationExtensions\ResponseExtension;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ScrambleServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function generateForRoute(\Closure $param, array $extensions = [])
    {
        if (! method_exists(Scramble::class, 'configure')) {
            return $this->generateForRoutePre0dot12($param, $extensions);
        }

        $route = $param();

        $config = Scramble::configure()
            ->useConfig(config('scramble'))
            ->routes(fn (Route $r) => $r->uri === $route->uri)
            ->withOperationTransformers(array_merge(
                [
                    RequestEssentialsExtension::class,
                    RequestBodyExtension::class,
                    ErrorResponsesExtension::class,
                    ResponseExtension::class,
                    DeprecationExtension::class,
                ],
                $extensions
            ));

        return app()->make(Generator::class)($config);
    }

    /**
     * @throws BindingResolutionException
     */
    public function generateForRoutePre0dot12(\Closure $param, array $extensions)
    {
        $route = $param();

        app()->when(OperationBuilder::class)
            ->needs('$extensionsClasses')
            ->give(function () use ($extensions) {
                return array_merge([
                    RequestEssentialsExtension::class,
                    RequestBodyExtension::class,
                    ErrorResponsesExtension::class,
                    ResponseExtension::class,
                    DeprecationExtension::class,
                ], $extensions);
            });

        Scramble::routes(static fn (Route $r) => $r->uri === $route->uri);

        return app()->make(Generator::class)();
    }
}
