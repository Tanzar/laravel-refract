<?php

namespace Tanzar\Refract\Splitter;

use Illuminate\Database\Eloquent\Model;
use Tanzar\Refract\Models\RefractSplitter;

abstract class Splitter
{
    private RequiredParams $requiredKeys;

    private RefractSplitter $details;

    public function __construct()
    {
        $this->requiredKeys = new RequiredParams();
        $this->requiredParams($this->requiredKeys);
        $this->loadDetails();
    }

    abstract protected function requiredParams(RequiredParamsInterface $params): void;

    private function loadDetails(): void
    {
        $details = RefractSplitter::where('splitter_type', static::class)
            ->where('model_type', $this->modelClass())
            ->where('encoded_params', $this->requiredKeys->encode())
            ->first();

        if ($details === null) {
            $details = new RefractSplitter();
            $details->splitter_type = static::class;
            $details->model_type = $this->modelClass();
            $details->encoded_params = $this->requiredKeys->encode();
            $details->save();   
        }

        $this->details = $details;
    }

    final public function split(Model $model): ?SplitterParams
    {
        if ($this->cannotProcess($model)) {
            return null;
        }

        $modelIdKey = $model->getKeyName();

        $params = new SplitterParams(
            $this->requiredKeys,
            $this->modelValue($model),
            $model->$modelIdKey
        );
        $this->process($model, $params);
        return $params;
    }

    private function cannotProcess(Model $model): bool
    {
        return $model::class !== $this->modelClass();
    }

    abstract public function modelClass(): string;

    protected function modelValue(Model $model): float
    {
        return 1.0;
    }

    abstract protected function process(Model $model, SplitterParamsInterface $params): void;

    /**
     * Returns an array of relations that should be loaded for the model before processing.
     *
     * @return array<string>
     */
    public function relations(): array
    {
        return [];
    }

    public function getDetails(): RefractSplitter
    {
        return $this->details;
    }

    public function queue(): string
    {
        return config('refract.splitter.queue', 'default');
    }
}
