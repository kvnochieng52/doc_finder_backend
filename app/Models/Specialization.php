<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        // add other fields as needed
    ];

    /**
     * Get the facilities that have this specialization
     */
    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'facility_specialities',
            'speciality_id',
            'facility_id'
        );
    }
}
