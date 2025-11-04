<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    
    public $timestamps = false;

    protected $fillable = [
        'book_id',
        'book_name',
        'order_date',
        'status'
    ];

    protected $casts = [
        'order_date' => 'datetime'
    ];
}

