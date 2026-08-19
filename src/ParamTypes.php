<?php

namespace Tanzar\Refract;

enum ParamTypes : string
{
    case DATE = 'date';
    case INTEGER = 'int';
    case FLOAT = 'float';
    case STRING = 'string';
    case BOOLEAN = 'bool';
}
