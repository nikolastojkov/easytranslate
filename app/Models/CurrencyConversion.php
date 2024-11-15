<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_currency',
        'target_currency',
        'value',
        'converted_value',
        'rate'
    ];

    public const string SOURCE_CURRENCY = 'source_currency';
    public const string TARGET_CURRENCY = 'target_currency';
    public const string VALUE = 'value';
    public const string CONVERTED_VALUE = 'converted_value';
    public const string RATE = 'rate';
}
