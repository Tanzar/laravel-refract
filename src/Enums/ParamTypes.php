<?php

namespace Tanzar\Refract\Enums;

enum ParamTypes : string
{
    case DATE = 'date';
    case INTEGER = 'int';
    case FLOAT = 'float';
    case STRING = 'string';
    case BOOLEAN = 'bool';
}
