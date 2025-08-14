<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilitySpeciality extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id',
        'speciality_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the facility
     */
    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Get the specialization
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'speciality_id');
    }
}
