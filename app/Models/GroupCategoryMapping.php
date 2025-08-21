<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupCategoryMapping extends Model
{
    use HasFactory;

    protected $table = 'group_category_mappings';

    protected $fillable = [
        'group_id',
        'category_id'
    ];

    protected $casts = [
        'group_id' => 'integer',
        'category_id' => 'integer'
    ];

    // Optional: Add relationships if needed later
    // public function group()
    // {
    // return $this->belongsTo(Group::class);
    // }

    // public function category()
    // {
    // return $this->belongsTo(GroupCategory::class, 'category_id');
    // }
}
