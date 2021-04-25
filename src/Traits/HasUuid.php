<?php

declare(strict_types=1);

namespace Idunis\Context\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    public static $fakeUuid = null;

    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model): void {
            if (is_null($model->uuid)) {
                $model->uuid = static::$fakeUuid ?? (string)Str::uuid();
            }
        });
    }

    public static function find($uuid): ?Model
    {
        return static::where('id', $uuid)->orWhere('uuid', $uuid)->first();
    }
}
