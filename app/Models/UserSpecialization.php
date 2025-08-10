<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpecialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialization_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'specialization_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];
}
