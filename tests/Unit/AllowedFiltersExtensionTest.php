<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use Exonn\ScrambleSpatieQueryBuilder\AllowedFiltersExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate;
use Illuminate\Support\Facades\Route;

uses(TestCase::class);

test('test AllowedFiltersExtensions', function () {

    $queryParam = 'filter';

    app('config')->set('query-builder.parameters.filter', $queryParam);

    $result = $this->generateForRoute(
        fn() => Route::get('test', [AllowedFiltersExtensionController::class, 'a']),
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

class AllowedFiltersExtensionController extends \Illuminate\Routing\Controller
{
    public function a(): Illuminate\Http\Resources\Json\JsonResource
    {
        \Spatie\QueryBuilder\QueryBuilder::for(null)
            ->allowedFilters(['foo', 'bar']);

        return $this->unknown_fn();
    }
}
