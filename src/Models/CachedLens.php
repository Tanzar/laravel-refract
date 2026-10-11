<?php

namespace Tanzar\Refract\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property class-string $lens_type
 * @property string $filters_hash
 * @property array<string, mixed> $filters
 * @property Carbon $last_requested_at
 */
#[Table(name: 'refract_cached_lens', timestamps: false)]
class CachedLens
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
        ];
    }
}
