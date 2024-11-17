<?php

namespace App\Exceptions;

use Exception;

class DatabaseOperationException extends Exception
{
    protected $message = 'A database error occurred.';
}
