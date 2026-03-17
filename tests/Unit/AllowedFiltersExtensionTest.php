<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use Exonn\ScrambleSpatieQueryBuilder\AllowedFiltersExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\QueryBuilder;

uses(TestCase::class);

test('test AllowedFiltersExtensions',
    /**
     * @throws BindingResolutionException
     */
    function () {

        $queryParam = 'filter';

        app('config')->set('query-builder.parameters.filter', $queryParam);

        $result = $this->generateForRoute(
            fn () => Route::get('test', [AllowedFiltersExtensionController::class, 'a']),
            [AllowedFiltersExtension::class]
        );

        expect($result['paths']['/test']['get']['parameters'][0])->toBe([
            'name' => $queryParam,
            'in' => 'query',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'foo' => [
                        'type' => 'string',
                    ],
                    'bar' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'example' => ['[name]=john', '[email]=gmail'],
        ]);

    });

class AllowedFiltersExtensionController extends Controller
{
    public function a(): Illuminate\Http\Resources\Json\JsonResource
    {
        QueryBuilder::for(null)
            ->allowedFilters(['foo', 'bar']);

        return $this->unknown_fn();
    }
}
