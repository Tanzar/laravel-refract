<?php

namespace Tanzar\Refract\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Tanzar\Refract\ParamTypes;

/**
 * @property int $id
 * @property ParamTypes $type
 * @property Carbon $date_value
 * @property int $int_value
 * @property float $float_value
 * @property string $string_value
 * @property bool $bool_value
 */
#[Table(name: 'refract_params', timestamps: false)]
class RefractParam extends Model
{
     /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ParamTypes::class,
            'date_value' => 'date',
            'bool_value' => 'boolean'
        ];
    }
}
