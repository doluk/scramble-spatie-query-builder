<?php

namespace Exonn\ScrambleSpatieQueryBuilder;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Combined\AnyOf;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

class AllowedSortsExtension extends OperationExtension
{
    use Hookable;

    const string MethodName = 'allowedSorts';

    public array $examples = ['title', '-title', 'title,-id'];

    public string $configKey = 'query-builder.parameters.sort';

    public function handle(Operation $operation, RouteInfo $routeInfo)
    {
        $helper = new InferHelper;

        $methodCall = Utils::findMethodCall($routeInfo, self::MethodName);

        if (! $methodCall) {
            return;
        }

        $values = $helper->inferValues($methodCall, $routeInfo);
        $arrayType = new ArrayType;
        $arrayType->items->enum(array_merge(
            $values,
            array_map(static fn ($value) => '-'.$value, $values)
        ));
        $values_string = implode(' ,', array_map(static fn ($value) => '`'.$value . '`', $values));
        $parameter = new Parameter(config($this->configKey), 'query');
        $example = $values[0];
        if (count($values) > 1){
            $example = $values[1] . ',-' . $values[0];
        }

        $parameter->description('Available sorts are ' . $values_string . '. You can sort by multiple options by separating them with `,` . To sort in descending order, use `-` sign in front of the sort, for example: `-' . $values[0] . '`');
        $parameter->setSchema(Schema::fromType(new StringType))->example($example);

        $halt = $this->runHooks($operation, $parameter);
        if (! $halt) {
            $operation->addParameters([$parameter]);
        }
    }
}
