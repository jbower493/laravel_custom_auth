<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomSession extends Model
{
    use HasFactory;

    const SESSION_TYPE_APP = 0;
    const SESSION_TYPE_WEB = 1;

    protected $fillable = [
        'user_id',
        'type'
    ];
}
