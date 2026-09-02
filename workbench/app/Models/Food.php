<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Workbench\Database\Factories\FoodFactory;

/**
 * Summary of Food
 * 
 * @property int $id
 * @property string $name
 * @property float $price
 * @property string $category
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 */
#[UseFactory(FoodFactory::class)]
class Food extends Model
{
    /** @use HasFactory<FoodFactory> */
    use HasFactory, SoftDeletes;


}
