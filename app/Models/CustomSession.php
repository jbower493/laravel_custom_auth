<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class CustomSession extends Model
{
    use HasFactory, HasUlids;

    const SESSION_TYPE_APP = 0;
    const SESSION_TYPE_WEB = 1;
    const SESSION_LIFETIME_HOURS = 168; // 1 week

    protected $fillable = [
        'user_id',
        'type',
        'additional_user_id',
        'expires_at'
    ];
}
