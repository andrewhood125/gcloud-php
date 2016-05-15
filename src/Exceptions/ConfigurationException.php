<?php

namespace Andrewhood125\Exceptions;

class ConfigurationException extends \Exception
{
    public function __construct($message) {
        parent::__construct($message);
    }
}
