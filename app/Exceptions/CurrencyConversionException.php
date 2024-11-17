<?php

namespace App\Exceptions;

use Exception;

class CurrencyConversionException extends Exception
{
    protected $message = 'An error occurred during the currency conversion process.';
}