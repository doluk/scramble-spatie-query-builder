<?php

namespace Exonn\ScrambleSpatieQueryBuilder\Tests\Unit;

use App\Models\Project;
use Exonn\ScrambleSpatieQueryBuilder\AllowedFiltersExtension;
use Exonn\ScrambleSpatieQueryBuilder\Tests\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\QueryBuilder;

uses(TestCase::class);

test('it infers integer type for projectID filter',
    /**
     * @throws BindingResolutionException
     */
    function () {
        $queryParam = 'filter';
        app('config')->set('query-builder.parameters.filter', $queryParam);

        $result = $this->generateForRoute(
            fn () => Route::get('test', [TypeInferenceController::class, 'index']),
            [AllowedFiltersExtension::class]
        );

        $properties = $result['paths']['/test']['get']['parameters'][0]['schema']['properties'];

        expect($properties['projectID']['type'])->toBe('integer');
    });

class TypeInferenceController extends Controller
{
    public function index(): JsonResource
    {
        QueryBuilder::for(Project::class)
            ->allowedFilters(['projectID', 'status']);

        return $this->unknown_fn();
    }
}
