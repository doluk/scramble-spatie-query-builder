<?php

namespace Exonn\ScrambleSpatieQueryBuilder;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Combined\AnyOf;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\BooleanType;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\NumberType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\Type;
use Dedoc\Scramble\Support\Generator\TypeTransformer;
use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\Type\ObjectType as InferObjectType;
use Dedoc\Scramble\Infer\Definition\ClassDefinition;
use ReflectionClass;
use Throwable;

class AllowedFiltersExtension extends OperationExtension
{
    use Hookable;

    const string MethodName = 'allowedFilters';

    public array $examples = ['[name]=john', '[email]=gmail'];

    public string $configKey = 'query-builder.parameters.filter';

    public function handle(Operation $operation, RouteInfo $routeInfo)
    {
        $helper = new InferHelper;

        $methodCall = Utils::findMethodCall($routeInfo, self::MethodName);

        if (! $methodCall) {
            return;
        }

        $values = $helper->inferValues($methodCall, $routeInfo);

        $modelClass = Utils::findModel($methodCall);
        $inferModelDefinition = $modelClass ? $this->infer->analyzeClass($modelClass) : null;



        foreach ($values as $value) {
            $parameter = new Parameter(config($this->configKey)."[$value]", 'query');
            $type = $this->inferType($modelClass, $inferModelDefinition, $value);
            $types = [$type];
            if (!$type instanceof StringType){
                $types[] = new StringType;
            }
            $parameter->setSchema(Schema::fromType(new StringType()));
            if ($type instanceof StringType){
                $parameter->example("name");
            }
            elseif ($type instanceof NumberType){
                $parameter->example("1");
            }
            $parameter->description("Filter by `$value`. Multiple filter values can be provided using `,` as a separator.");
            $halt = $this->runHooks($operation, $parameter);
            if (! $halt) {
                $operation->addParameters([$parameter]);
            }
        }
    }

    private function inferType(?string $modelClass, ?ClassDefinition $inferModelDefinition, string $value): Type
    {
        if ($inferModelDefinition instanceof ClassDefinition) {
            $propertyDefinition = $inferModelDefinition->getPropertyDefinition($value);
            if ($propertyDefinition) {
                return $this->openApiTransformer->transform($propertyDefinition->type);
            }
        }

        if ($modelClass && class_exists($modelClass)) {
            try {
                $reflection = new ReflectionClass($modelClass);
                $docComment = $reflection->getDocComment();
                if ($docComment && preg_match('/@property\s+([^\s]+)\s+\$'.$value.'/', $docComment, $matches)) {
                    $typeStr = $matches[1];
                    if (in_array($typeStr, ['int', 'integer'])) {
                        return new IntegerType;
                    }
                    if (in_array($typeStr, ['bool', 'boolean'])) {
                        return new BooleanType;
                    }
                    if (in_array($typeStr, ['float', 'double'])) {
                        return new NumberType;
                    }
                }
            } catch (Throwable $e) {
            }
        }

        return new StringType;
    }
}
