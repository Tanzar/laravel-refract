<?php

namespace Tanzar\Refract\Enums;

enum ParamTypes : string
{
    case DATE = 'date';
    case INTEGER = 'int';
    case FLOAT = 'float';
    case STRING = 'string';
    case BOOLEAN = 'bool';

    public function column(): string
    {
        return match ($this) {
            ParamTypes::DATE => 'date_value',
            ParamTypes::INTEGER => 'int_value',
            ParamTypes::FLOAT => 'float_value',
            ParamTypes::STRING => 'string_value',
            ParamTypes::BOOLEAN => 'bool_value',
        };
    }
}
