<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use Exonn\ScrambleSpatieQueryBuilder\AllowedSortsExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate;
use Illuminate\Support\Facades\Route;

uses(TestCase::class);

test('test AllowedSortsExtensions', function () {

    $queryParam = 'sort';

    app('config')->set('query-builder.parameters.sort', $queryParam);

    $result = $this->generateForRoute(
        fn() => Route::get('test', [AllowedSortsExtensionController::class, 'a']),
        [AllowedSortsExtension::class]
    );

    expect($result['paths']['/test']['get']['parameters'][0])->toBe([
        'name' => $queryParam,
        'in' => 'query',
        'schema' => [
            'anyOf' => [
                [
                    'type' => 'string',
                ],
                [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => [
                            'foo',
                            'bar',
                            '-foo',
                            '-bar',
                        ],
                    ],
                ],

            ],
        ],
        'example' => ['title', '-title', 'title,-id'],
    ]);

});

class AllowedSortsExtensionController extends \Illuminate\Routing\Controller
{
    public function a(): Illuminate\Http\Resources\Json\JsonResource
    {
        \Spatie\QueryBuilder\QueryBuilder::for(null)
            ->allowedSorts(['foo', 'bar']);

        return $this->unknown_fn();
    }
}
