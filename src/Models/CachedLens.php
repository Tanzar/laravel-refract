<?php

namespace Tanzar\Refract\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Tanzar\Refract\Enums\LensStates;

/**
 * @property int $id
 * @property class-string $lens_type
 * @property string $filters_hash
 * @property array<string, mixed> $filters
 * @property LensStates $state
 * @property array<string, mixed> $meta_data
 * @property Carbon $last_requested_at
 */
#[Table(name: 'refract_cached_lens', timestamps: false)]
class CachedLens extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_requested_at' => 'datetime',
            'filters' => 'array',
            'state' => LensStates::class,
            'meta_data' => 'array'
        ];
    }
}
