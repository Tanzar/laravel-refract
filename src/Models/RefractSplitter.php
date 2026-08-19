<?php

namespace Tanzar\Refract\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $splitter_type
 * @property string $model_type
 * @property int $bands_count
 * @property string $encoded_params
 */
#[Table(name: 'refract_splitters', timestamps: false)]
class RefractSplitter extends Model
{
    
}
