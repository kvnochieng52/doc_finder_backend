<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupSubcategoryMapping extends Model
{
    use HasFactory;

    protected $table = 'group_subcategory_mappings';

    protected $fillable = [
        'group_id',
        'subcategory_id'
    ];

    protected $casts = [
        'group_id' => 'integer',
        'subcategory_id' => 'integer'
    ];

    // Optional: Add relationships if needed later
    // public function group()
    // {
    // return $this->belongsTo(Group::class);
    // }

    // public function subcategory()
    // {
    // return $this->belongsTo(GroupSubCategory::class, 'subcategory_id');
    // }
}
