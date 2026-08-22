<?php

namespace Tanzar\Refract\Exceptions;


class RefractException extends \Exception
{
    public function __construct(string $message = "")
    {
        parent::__construct("Refract error: $message");
    }
}
