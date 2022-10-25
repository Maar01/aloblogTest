<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class WeakModel extends \Illuminate\Support\Fluent
{
    protected $table;

    private function baseQuery()
    {
        return DB::table($this->table ?? get_called_class() . 's');
    }

    protected function all(): Collection
    {
        return $this->baseQuery()->get()->map(function ($record) {
            return new static($record);
        });
    }

    protected  function delete()
    {
        return $this->baseQuery()->delete();
    }

    protected function find(int $id)
    {
        $record = $this->baseQuery()->where('id', $id)->first();
        return $record
            ? new static($record)
            : null;
    }

    protected function store(array $data)
    {
        $id = $this->baseQuery()->insertGetId($data);
        return $this->find($id);
    }

    public static function __callStatic($method, $parameters)
    {
        if (method_exists(self::class, $method)) {
            return (new static())->$method(...$parameters);
        }
    }
}
