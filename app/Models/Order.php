<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'book_id',
        'book_name',
        'order_date',
        'status'
    ];

    protected $dates = ['order_date'];

    public $timestamps = false;
}
