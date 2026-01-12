<?php

namespace Exonn\ScrambleSpatieQueryBuilder;

use Dedoc\Scramble\Support\RouteInfo;
use PhpParser\Node;
use PhpParser\NodeFinder;

class Utils
{
    public static function findMethodCall(RouteInfo $routeInfo, string $methodName): ?Node\Expr\MethodCall
    {
        $methodNode = $routeInfo->actionNode();
        if (!$methodNode) {
             return null;
        }

        /** @var Node\Expr\MethodCall|null $methodCall */
        $methodCall = (new NodeFinder)->findFirst(
            $methodNode,
            fn (Node $node) =>
                // todo: check if the methodName is called on QueryBuilder
                $node instanceof Node\Expr\MethodCall &&
                $node->name instanceof Node\Identifier &&
                $node->name->name === $methodName
        );

        return $methodCall;
    }

    public static function findModel(Node\Expr\MethodCall $methodCall): ?string
    {
        $var = $methodCall->var;
        while ($var instanceof Node\Expr\MethodCall) {
            if ($var->name instanceof Node\Identifier && $var->name->name === 'for') {
                return self::extractModel($var->args[0]->value ?? null);
            }
            $var = $var->var;
        }

        if ($var instanceof Node\Expr\StaticCall && $var->name instanceof Node\Identifier && $var->name->name === 'for') {
            return self::extractModel($var->args[0]->value ?? null);
        }

        return null;
    }

    private static function extractModel(?Node\Expr $expr): ?string
    {
        if (! $expr) {
            return null;
        }

        // Model::class
        if ($expr instanceof Node\Expr\ClassConstFetch && $expr->name instanceof Node\Identifier
            && $expr->name->name === 'class' && $expr->class instanceof Node\Name) {
            return $expr->class->toString();
        }

        // Model::accessible(), Model::query(), etc.
        if ($expr instanceof Node\Expr\StaticCall && $expr->class instanceof Node\Name) {
            return $expr->class->toString();
        }

        return null;
    }
}
