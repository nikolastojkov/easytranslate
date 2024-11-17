<?php

namespace App\Exceptions;

use Exception;

class InvalidCurrencyException extends Exception
{
    protected $message = 'The provided currency is invalid.';
}