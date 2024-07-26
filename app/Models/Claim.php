<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use SoftDeletes;

    // Append the computed attribute
    protected $appends = ['py_admin_fee'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['sin', 'first_name', 'last_name', 'dob', 'email', 'city', 'zip_code',
        'claim_type', 'course_name', 'claim_status', 'claimed_by_user_guid', 'claimed_date',
        'registration_fee', 'materials_fee', 'program_fee', 'claim_percent',
        'estimated_hold_amount', 'total_claim_amount',
        'stable_enrolment_date', 'expiry_date', 'psi_claim_request_date', 'reporting_completed_date',
        'fifty_two_week_affirmation', 'agreement_confirmed', 'registration_confirmed',
        'guid', 'institution_guid', 'allocation_guid', 'program_guid', 'student_guid', 'expected_stable_enrolment_date',
        'expected_completion_date',];

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_guid', 'guid');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_guid', 'guid');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_guid', 'guid');
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class, 'allocation_guid', 'guid');
    }

    // Define the accessor for the computed attribute
    public function getPyAdminFeeAttribute()
    {
        return $this->allocation->py->claim_percent;
    }
}
