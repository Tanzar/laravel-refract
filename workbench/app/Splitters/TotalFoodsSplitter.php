<?php

namespace Workbench\App\Splitters;

use Tanzar\Refract\Splitter\RequiredParamsInterface;
use Override;
use Illuminate\Database\Eloquent\Model;
use Tanzar\Refract\Splitter\Splitter;
use Tanzar\Refract\Splitter\SplitterParamsInterface;
use Workbench\App\Models\Food;

class TotalFoodsSplitter extends Splitter
{
    #[Override]
    protected function requiredParams(RequiredParamsInterface $params): void
    {
        $params->string('category', 'general')
            ->float('price', 0.0);
    }

    #[Override]
    public static function modelClass(): string
    {
        return Food::class;
    }

    #[Override]
    protected function process(Model $model, SplitterParamsInterface $params): void
    {
        $params->string('category', $model->category);
        $params->float('price', $model->price);
    }
}
