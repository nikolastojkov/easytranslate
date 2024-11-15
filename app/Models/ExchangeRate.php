<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_currency',
        'target_currency',
        'rate',
        'fetched_at'
    ];

    public const string SOURCE_CURRENCY = 'source_currency';
    public const string TARGET_CURENCY = 'target_currency';
    public const string RATE = 'rate';
    public const string FETCHED_AT = 'fetched_at';

    protected $dates = ['fetched_at'];
}
