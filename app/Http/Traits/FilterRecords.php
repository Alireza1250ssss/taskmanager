<?php

namespace App\Http\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait FilterRecords
{
    public Builder $queryHandler;
    public array $requestFilters;

    // you should declare $filter property in your model and define
    // fields that can be filtered

    public static function getRecords($filters)
    {
        $model = new static();
        $result = $model->orderBy($filters['order_by'] ?? $model->primaryKey, $filters['order_mode'] ?? 'DESC');
        if (isset($filters['with_trashed']) && $filters['with_trashed'] == true)
            $result->withTrashed();
        $modelFilters = array_filter($filters, fn($key) => in_array($key, $model->filters), ARRAY_FILTER_USE_KEY);
        static::doQuery($result, $modelFilters);

        $relations = static::getModelRelations();
        static::filterRelations($result, $filters, $relations['reflector'], $relations['methodNames']);
        $model->queryHandler = &$result;
        $model->requestFilters = $filters;
        return $model;
//        return $result->paginate($filters['limit'] ?? 10);
    }


    public static function filterRelations(&$data, $filters, $reflector, $methodNames)
    {
        foreach ($methodNames as $methodName) {
            $method = $reflector->getMethod($methodName);
            $returnType = $method->getReturnType();
            $relatedModel = $method->invoke(new static)->getRelated();
            $newFilters = collect($filters);
            $newFilters = $newFilters
                ->filter(fn($filter, $key) => Str::startsWith($key, $methodName . "_"))
                ->mapWithKeys(function ($filter, $key) use ($methodName) {
                    $key = str_replace($methodName . "_", '', $key);
                    return [$key => $filter];
                })
                ->filter(fn($filter, $key) => in_array($key, $relatedModel->filters))
                ->toArray();
            if (!empty($newFilters))
                if (str_contains($returnType, "BelongsTo") || !isset($filters['strict_for_' . $methodName]))
                    $data->whereHas($methodName, function ($query) use ($newFilters) {
                        static::doQuery($query, $newFilters);
                    });
                else
                    $data->whereDoesntHave($methodName, function ($query) use ($newFilters) {
                        static::doQuery($query, $newFilters, true);
                    });
        }

    }

    public static function doQuery(&$queryHandler, $filters, bool $forRelations = false)
    {
        foreach ($filters as $field => $value) {
            if (Str::startsWith($field, 'max_'))
                $queryHandler->where(explode('max_', $field)[1], $forRelations ? ">=" : '<=', $value);
            elseif (Str::startsWith($field, 'min'))
                $queryHandler->where(explode('min_', $field)[1], $forRelations ? "<=" : '>=', $value);
            elseif (Str::startsWith('in_', $field)) {
                if ($forRelations)
                    $queryHandler->whereNotIn(explode('in_', $field)[1], explode(",", $value));
                else
                    $queryHandler->whereIn(explode('in_', $field)[1], explode(",", $value));
            }
            elseif (Str::endsWith($field,'ref_id'))
                $queryHandler->where($field,$forRelations ? '!=' : '=',$value);
            else
                $queryHandler->where($field, $forRelations ? "not like" : 'like', "%$value%");
        }
    }

    public static function getModelRelations(): array
    {
        $reflector = new \ReflectionClass(static::class);


        // This line returns the related model
        // $method = $reflector->getMethod('zonePoints');
        // $return = $method->invoke(new static)->getRelated();

        $methodNames = collect($reflector->getMethods())
            ->filter(
                fn($method) => !empty($method->getReturnType()) &&
                    str_contains($method->getReturnType(), "Illuminate\Database\Eloquent\Relations")
            )
            ->pluck('name')
            ->all();
        return compact('reflector', 'methodNames');
    }

    /**
     * Fetching data and send it back
     *
     * @return LengthAwarePaginator
     */
    public function get(): LengthAwarePaginator
    {
        return $this->queryHandler->paginate($this->requestFilters['limit'] ?? 10);
    }

    /**
     * This method is used to add more constraints on query , you should call 'get' method afterwards to retrieve
     *
     * @param callable $closure
     * @return $this
     */
    public function addConstraints(callable $closure)
    {
        $query = &$this->queryHandler;
        call_user_func_array($closure, [$query]);
        return $this;
    }
}
