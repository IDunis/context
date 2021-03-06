<?php

declare(strict_types=1);

namespace Idunis\Context\ORM\Eloquent;

use Idunis\Context\AggregateRoots\AggregateRootId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trait UuidModel
 * @package App\Traits
 */
trait UuidModel
{
    /**
     * Binds creating/saving events to create UUIDs (and also prevent them from being overwritten).
     *
     * @return void
     */
    public static function bootUuidModel()
    {
        static::creating(function (Model $model): void {
            if (is_null($model->uuid)) {
                $model->uuid = AggregateRootId::create()->toString();
            }
        });
    }

    public function getUuid()
    {
        return $this->uuid;
    }
    
    /**
     * Scope a query to only include models matching the supplied UUID.
     * Returns the model by default, or supply a second flag `false` to get the Query Builder instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @param  \Illuminate\Database\Schema\Builder $query The Query Builder instance.
     * @param  string                              $uuid  The UUID of the model.
     * @param  bool|true                           $first Returns the model by default, or set to `false` to chain for query builder.
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeUuid($query, $uuid, $first = true)
    {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', (string)$uuid) !== 1)) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }
    
        $search = $query->where('uuid', $uuid);
    
        return $first ? $search->firstOrFail() : $search;
    }
    
    /**
     * Scope a query to only include models matching the supplied ID or UUID.
     * Returns the model by default, or supply a second flag `false` to get the Query Builder instance.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @param  \Illuminate\Database\Schema\Builder $query The Query Builder instance.
     * @param  string                              $uuid  The UUID of the model.
     * @param  bool|true                           $first Returns the model by default, or set to `false` to chain for query builder.
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeIdOrUuId($query, $id_or_uuid, $first = true)
    {
        if (!is_string($id_or_uuid) && !is_numeric($id_or_uuid)) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }
    
        if (preg_match('/^([0-9]+|[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/', (string)$id_or_uuid) !== 1) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }
    
        $search = $query->where(function ($query) use ($id_or_uuid) {
            $query->where('id', is_numeric($id_or_uuid) ? $id_or_uuid : null)
                ->orWhere('uuid', $id_or_uuid);
        });
    
        return $first ? $search->firstOrFail() : $search;
    }

    public static function find($id_or_uuid): ?Model
    {
        return static::where('id', is_numeric($id_or_uuid) ? $id_or_uuid : null)->orWhere('uuid', $id_or_uuid)->first();
    }

    public static function findOrFail($id_or_uuid): ?Model
    {
        $entity = static::where('id', is_numeric($id_or_uuid) ? $id_or_uuid : null)->orWhere('uuid', $id_or_uuid)->first();
        if (!$entity) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }
        return $entity;
    }
}