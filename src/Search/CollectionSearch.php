<?php

namespace Guava\Search\Search;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Pipeline;

class CollectionSearch extends Search
{
    public function __construct(
        protected Collection $collection,
        protected array      $queryFilters = [],
    )
    {
    }

    public function getQueryFilter(string $key, $default = null)
    {
        return Arr::get($this->queryFilters, $key, $default);
    }

    public function collection(Closure|Collection $collection): static
    {
        if ($collection instanceof Closure) {
            $this->collection = $collection($this->collection);
        } else {
            $this->collection = $collection;
        }

        return $this;
    }

    public function getCollection(): Collection
    {
        return Pipeline::send($this)
            ->through([
                ...$this->getFilters(),
            ])
            ->then(fn(CollectionSearch $search) => $search->collection);
    }

    public static function make(
        array|Collection|EloquentSearch $collection,
        array                           $queryFilters = [],
    ): static
    {
        if (is_array($collection)) {
            $collection = collect($collection);
        }

        if ($collection instanceof EloquentSearch) {
            $collection = $collection->getQuery()->get();
        }

        return app(static::class, [
            'collection' => $collection,
            'queryFilters' => $queryFilters,
        ]);
    }
}