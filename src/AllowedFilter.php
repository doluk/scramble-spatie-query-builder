<?php

namespace Exonn\ScrambleSpatieQueryBuilder;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter as SpatieAllowedFilter;

class AllowedFilter extends SpatieAllowedFilter
{
    const string FilterModesQueryParamConfigKey = 'query-builder.parameters.filter_mode';

    public static function autoDetect(Request $request, string $key, FilterModeEnum $default_mode = FilterModeEnum::Contains)
    {
        $mode = $request->input(config(self::FilterModesQueryParamConfigKey).'.'.$key) ?? $default_mode->value;

        return match ($mode) {
            FilterModeEnum::StartsWith->value => self::beginsWithStrict($key),
            FilterModeEnum::EndsWith->value => self::endsWithStrict($key),
            FilterModeEnum::Exact->value => self::exact($key),
            default => self::partial($key),
        };
    }
}
