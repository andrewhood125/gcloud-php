<?php

namespace Andrewhood125\Exceptions;

class MissingParameterException extends \Exception
{
    public function __construct($message) {
        parent::__construct($message);
    }
}
