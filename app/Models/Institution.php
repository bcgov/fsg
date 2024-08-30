<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    // Append the computed attribute
    protected $appends = ['overallocation_flag'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['guid', 'name', 'legal_name', 'address1', 'address2', 'primary_contact', 'category',
        'primary_email', 'city', 'postal_code', 'province', 'active_status', 'standing_status',
        'bceid_business_guid', 'last_touch_by_user_guid', 'economic_region', ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['last_touch_by_user_guid'];

    public function claims()
    {
        return $this->hasMany(Claim::class, 'institution_guid', 'guid')->orderBy('created_at');
    }

    public function activeClaims()
    {
        return $this->hasMany(Claim::class, 'institution_guid', 'guid')->where('claim_status', '!=', 'draft')->orderBy('created_at');
    }

    public function programs()
    {
        return $this->hasMany(Program::class, 'institution_guid', 'guid')->orderBy('program_name');
    }

    public function activePrograms()
    {
        return $this->hasMany(Program::class, 'institution_guid', 'guid')
            ->where('active_status', true)
            ->orderBy('program_name');
    }

    public function allocations()
    {
        return $this->hasMany(Allocation::class, 'institution_guid', 'guid')->orderByDesc('created_at');
    }

    public function activeAllocation()
    {
        return $this->hasOne(Allocation::class, 'institution_guid', 'guid')
            ->where('status', 'active');
    }

    public function staff()
    {
        return $this->hasMany(InstitutionStaff::class, 'institution_guid', 'guid')->whereHas('user');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, InstitutionStaff::class,
            'institution_guid', 'guid', 'guid', 'user_guid');
    }

    /**
     * Scope a query to only include admin users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active_status', true);
    }

    // Define the accessor for the computed attribute
    public function getOverallocationFlagAttribute()
    {
        $activeAllocation = $this->activeAllocation;
        if (! $activeAllocation) {
            return false; // No active allocation found, no overallocation
        }

        $programYear = $activeAllocation->py;
        $claimPercent = $programYear ? $programYear->claim_percent : 1; // Default to 1 if not found

        // Calculate the claimed amount
        $claimedAmount = $this->claims()
            ->where('allocation_guid', $this->activeAllocation->guid)
            ->where('claim_status', 'Claimed')
            ->sum(\DB::raw('registration_fee + materials_fee + program_fee'));

        $this->setClaimedAmountAttribute($claimedAmount);

        //$claimedAmount needs to be a formatted number to avoid = "1.159671e+06" since it is going to too large
        //        $claimed = number_format($claimedAmount, 2);

        // Calculate the overallocation flag
        $overAllocationFlag = (float) $this->activeAllocation->total_amount >
            (((float) $this->activeAllocation->total_amount -
                    $claimedAmount)
                * 1.1 * $claimPercent);

        return $overAllocationFlag;
    }

    public function setClaimedAmountAttribute($value)
    {
        $this->attributes['claimed_amount'] = $value;
    }
}
