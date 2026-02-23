<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use App\Models\Project;
use Exonn\ScrambleSpatieQueryBuilder\AllowedFiltersExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate\Support\Facades\Route;

uses(TestCase::class);

test('it infers integer type for projectID filter', function () {
    $queryParam = 'filter';
    app('config')->set('query-builder.parameters.filter', $queryParam);

    $result = $this->generateForRoute(
        fn() => Route::get('test', [TypeInferenceController::class, 'index']),
        [AllowedFiltersExtension::class]
    );

    $properties = $result['paths']['/test']['get']['parameters'][0]['schema']['properties'];

    expect($properties['projectID']['type'])->toBe('integer');
});

class TypeInferenceController extends \Illuminate\Routing\Controller
{
    public function index(): \Illuminate\Http\Resources\Json\JsonResource
    {
        \Spatie\QueryBuilder\QueryBuilder::for(Project::class)
            ->allowedFilters(['projectID', 'status']);

        return $this->unknown_fn();
    }
}
