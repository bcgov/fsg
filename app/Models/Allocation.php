<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allocation extends Model
{
    use SoftDeletes;

    // Append the computed attribute
    protected $appends = ['total_amount_formatted', 'claimed', 'py_admin_fee'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['guid', 'institution_guid', 'program_year_guid', 'total_amount',
        'on_hold_amount', 'status', 'claimed_amount', ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['last_touch_by_user_guid'];

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_guid', 'guid');
    }

    public function py()
    {
        return $this->belongsTo(ProgramYear::class, 'program_year_guid', 'guid');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'allocation_guid', 'guid')->orderByDesc('created_at');
    }

    // Define the accessor for the computed attribute
    public function getClaimedAttribute()
    {
        // Calculate the claimed amount
        $claimedAmount = $this->claims()
            ->where('claim_status', 'Claimed')
            ->sum(\DB::raw('registration_fee + materials_fee + program_fee'));

        //$claimedAmount needs to be a formatted number to avoid = "1.159671e+06" since it is going to too large
        return number_format($claimedAmount, 0, '.', '');
    }

    // Define the accessor for the computed attribute
    public function getPyAdminFeeAttribute()
    {
        return $this->py->claim_percent;
    }

    // Add accessor for total_grant
    public function getTotalAmountFormattedAttribute()
    {
        return number_format($this->total_amount, 0, '.', '');
    }
}
