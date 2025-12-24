<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'sale_date',
        'invoice_number',
        'total_amount',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'integer',
    ];
}
