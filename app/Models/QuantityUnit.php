<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuantityUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol'
    ];

    protected $hidden = ['created_at', 'updated_at', 'user_id'];
}