<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use Exonn\ScrambleSpatieQueryBuilder\AllowedIncludesExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\QueryBuilder;

uses(TestCase::class);

test('test AllowedIncludesExtensions',
    /**
     * @throws BindingResolutionException
     */ function () {

        $queryParam = 'include';

        app('config')->set('query-builder.parameters.include', $queryParam);

        $result = $this->generateForRoute(
            fn () => Route::get('test', [AllowedIncludesExtensionController::class, 'a']),
            [AllowedIncludesExtension::class]
        );

        expect($result['paths']['/test']['get']['parameters'][0])->toBe([
            'name' => $queryParam,
            'in' => 'query',
            'schema' => [
                'anyOf' => [
                    [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'enum' => [
                                'foo',
                                'bar',
                            ],
                        ],
                    ],
                    [
                        'type' => 'string',
                    ],
                ],
            ],
            'example' => ['posts', 'posts.comments', 'books'],
        ]);

    });

class AllowedIncludesExtensionController extends Controller
{
    public function a(): Illuminate\Http\Resources\Json\JsonResource
    {
        QueryBuilder::for(null)
            ->allowedIncludes(['foo', 'bar']);

        return $this->unknown_fn();
    }
}
