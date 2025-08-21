<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'groups';

    protected $fillable = [
        'group_name',
        'group_description',
        'group_location',
        'group_tags',
        'group_privacy',
        'require_approval',
        'group_image',
        'cover_image',
        'created_by',
    ];

    protected $casts = [
        'require_approval' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Define the valid privacy options
    const PRIVACY_PUBLIC = 'public';
    const PRIVACY_PRIVATE = 'private';
    const PRIVACY_CLOSED = 'closed';

    public static function getPrivacyOptions(): array
    {
        return [
            self::PRIVACY_PUBLIC,
            self::PRIVACY_PRIVATE,
            self::PRIVACY_CLOSED,
        ];
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('group_privacy', self::PRIVACY_PUBLIC);
    }

    public function scopePrivate($query)
    {
        return $query->where('group_privacy', self::PRIVACY_PRIVATE);
    }

    public function scopeClosed($query)
    {
        return $query->where('group_privacy', self::PRIVACY_CLOSED);
    }

    public function scopeByPrivacy($query, $privacy)
    {
        return $query->where('group_privacy', $privacy);
    }

    public function scopeRequiringApproval($query)
    {
        return $query->where('require_approval', true);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    // Accessors
    public function getGroupImageUrlAttribute(): ?string
    {
        if ($this->group_image) {
            return asset('storage/' . $this->group_image);
        }
        return null;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return null;
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M d, Y');
    }

    public function getPrivacyLabelAttribute(): string
    {
        return match ($this->group_privacy) {
            self::PRIVACY_PUBLIC => 'Public',
            self::PRIVACY_PRIVATE => 'Private',
            self::PRIVACY_CLOSED => 'Closed',
            default => 'Unknown'
        };
    }

    // Mutators
    public function setGroupTagsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['group_tags'] = implode(',', $value);
        } else {
            $this->attributes['group_tags'] = $value;
        }
    }

    // Helper methods
    public function isPublic(): bool
    {
        return $this->group_privacy === self::PRIVACY_PUBLIC;
    }

    public function isPrivate(): bool
    {
        return $this->group_privacy === self::PRIVACY_PRIVATE;
    }

    public function isClosed(): bool
    {
        return $this->group_privacy === self::PRIVACY_CLOSED;
    }

    public function requiresApproval(): bool
    {
        return $this->require_approval;
    }

    public function isOwnedBy(int $userId): bool
    {
        return $this->created_by === $userId;
    }

    public function getTagsArray(): array
    {
        if (empty($this->group_tags)) {
            return [];
        }

        return array_map('trim', explode(',', $this->group_tags));
    }

    public function hasImage(): bool
    {
        return !empty($this->group_image);
    }

    public function hasCoverImage(): bool
    {
        return !empty($this->cover_image);
    }
}
