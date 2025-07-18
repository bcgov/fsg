<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDemographicShare extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_guid',
        'demographic_id',
        'shareable_entity_id',
        'is_shared',
        'shared_at',
        'revoked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_shared' => 'boolean',
        'shared_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the student that owns this share.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_guid', 'guid');
    }

    /**
     * Get the demographic that is being shared.
     */
    public function demographic()
    {
        return $this->belongsTo(Demographic::class);
    }

    /**
     * Get the entity that the demographic is shared with.
     */
    public function shareableEntity()
    {
        return $this->belongsTo(ShareableEntity::class);
    }

    /**
     * Get the student demographic record.
     */
    public function studentDemographic()
    {
        return $this->belongsTo(StudentDemographic::class, 'demographic_id', 'demographic_id')
            ->where('student_guid', $this->student_guid);
    }

    /**
     * Scope a query to only include shared records.
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope a query to only include active shares.
     */
    public function scopeActive($query)
    {
        return $query->where('is_shared', true)
            ->whereNull('revoked_at');
    }
}
