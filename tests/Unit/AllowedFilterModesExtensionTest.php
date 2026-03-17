<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use Exonn\ScrambleSpatieQueryBuilder\AllowedFilter;
use Exonn\ScrambleSpatieQueryBuilder\AllowedFilterModesExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\QueryBuilder;

uses(TestCase::class);

test('test AllowedFilterModesExtensions',
    /**
     * @throws BindingResolutionException
     */
    function () {

        $queryParam = 'filter_mode';

        app('config')->set(AllowedFilter::FilterModesQueryParamConfigKey, $queryParam);

        $result = $this->generateForRoute(
            fn () => Route::get('test', [AllowedFilterModesExtensionController::class, 'a']),
            [AllowedFilterModesExtension::class]
        );

        expect($result['paths']['/test']['get']['parameters'][0])->toBe([
            'name' => $queryParam,
            'in' => 'query',
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'foo' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'starts_with',
                                'ends_with',
                                'exact',
                                'partial',
                            ],
                        ],
                    ],
                    'bar' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'starts_with',
                                'ends_with',
                                'exact',
                                'partial',
                            ],
                        ],
                    ],
                ],

            ],
            'example' => ['[name]=starts_with', '[email]=exact'],
        ]);

    });

class AllowedFilterModesExtensionController extends Controller
{
    public function a(): Illuminate\Http\Resources\Json\JsonResource
    {
        QueryBuilder::for(null)
            ->allowedFilters(['foo', 'bar']);

        return $this->unknown_fn();
    }
}
