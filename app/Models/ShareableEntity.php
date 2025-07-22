<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareableEntity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'active',
        'contact_email',
        'contact_phone',
        'privacy_policy_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the demographic shares for this entity.
     */
    public function demographicShares()
    {
        return $this->hasMany(StudentDemographicShare::class);
    }

    /**
     * Get shared demographics for this entity.
     */
    public function sharedDemographics()
    {
        return $this->hasManyThrough(
            StudentDemographic::class,
            StudentDemographicShare::class,
            'shareable_entity_id',
            'id',
            'id',
            'demographic_id'
        )->where('student_demographic_shares.is_shared', true);
    }

    /**
     * Scope a query to only include active entities.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
