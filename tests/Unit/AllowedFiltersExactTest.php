<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use Exonn\ScrambleSpatieQueryBuilder\AllowedFiltersExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

uses(TestCase::class);

test('it can infer exact filters',
    /**
     * @throws BindingResolutionException
     */
    function () {

        $queryParam = 'filter';

        app('config')->set('query-builder.parameters.filter', $queryParam);

        $result = $this->generateForRoute(
            fn () => Route::get('test-exact', [AllowedFiltersExactController::class, 'index']),
            [AllowedFiltersExtension::class]
        );

        expect($result['paths']['/test-exact']['get']['parameters'][0]['schema']['properties'])->toHaveKey('personID');
    });

class AllowedFiltersExactController extends Controller
{
    public function index(): array
    {
        $projects = QueryBuilder::for(null)
            ->allowedFilters([
                AllowedFilter::exact('personID'),
            ]);

        return [];
    }
}
