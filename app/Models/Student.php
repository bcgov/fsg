<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    // Append the computed attribute
    protected $appends = ['can_apply'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['guid', 'user_guid', 'sin', 'first_name', 'last_name', 'dob', 'email', 'city', 'zip_code', 'total_grant',
        'citizenship', 'grade12_or_over19', 'info_consent', 'duplicative_funding', 'tax_implications', 'lifetime_max',
        'fed_prov_benefits', 'workbc_client', 'additional_supports', 'bc_resident', 'gender', ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_guid', 'guid');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'student_guid', 'guid')->orderBy('created_at');
    }

    public function applications()
    {
        return $this->hasMany(Claim::class, 'student_guid', 'guid')->orderBy('created_at');
    }

    // Add accessor for total_grant
    public function getCanApplyAttribute()
    {
        if(is_null($this->sin) || is_null($this->dob) || !$this->additional_supports || !$this->bc_resident ||
            !$this->duplicative_funding || !$this->fed_prov_benefits || !$this->info_consent || !$this->lifetime_max ||
            !$this->tax_implications || !$this->workbc_client){
            return false;
        }
        return true;
    }

    /**
     * Get the student demographics for this student.
     */
    public function demographics()
    {
        return $this->hasMany(StudentDemographic::class, 'student_guid', 'guid');
    }

    /**
     * Get the student demographic answers through demographics.
     */
    public function demographicAnswers()
    {
        return $this->hasManyThrough(
            StudentDemographicAnswer::class,
            StudentDemographic::class,
            'student_guid',
            'student_demographic_id',
            'guid',
            'id'
        );
    }

    /**
     * Get the demographic shares for this student.
     */
    public function demographicShares()
    {
        return $this->hasMany(StudentDemographicShare::class, 'student_guid', 'guid');
    }

    /**
     * Get entities that this student has shared demographics with.
     */
    public function sharedEntities()
    {
        return $this->hasManyThrough(
            ShareableEntity::class,
            StudentDemographicShare::class,
            'student_guid',
            'id',
            'guid',
            'shareable_entity_id'
        )->where('student_demographic_shares.is_shared', true);
    }

    /**
     * Get formatted demographic data for forms
     */
    public function getFormattedDemographics()
    {
        $demographics = [];
        
        $studentDemographics = $this->demographics()->with(['answers', 'demographic'])->get();
        
        foreach ($studentDemographics as $studentDemo) {
            $demographicId = $studentDemo->demographic_id;
            $answers = $studentDemo->answers;
            
            if ($answers->count() === 1) {
                // Single answer
                $demographics[$demographicId] = $answers->first()->value;
            } elseif ($answers->count() > 1) {
                // Multiple answers (checkboxes, multi-select)
                $demographics[$demographicId] = $answers->pluck('value')->implode(',');
            }
        }
        
        return $demographics;
    }

    /**
     * Get formatted demographic sharing data for forms
     */
    public function getFormattedDemographicShares()
    {
        $shares = [];
        
        $studentShares = $this->demographicShares()->with(['demographic', 'shareableEntity'])->get();
        
        foreach ($studentShares as $share) {
            $demographicId = $share->demographic_id;
            
            if (!isset($shares[$demographicId])) {
                $shares[$demographicId] = [
                    'demographic' => $share->demographic,
                    'entities' => []
                ];
            }
            
            $shares[$demographicId]['entities'][] = [
                'entity' => $share->shareableEntity,
                'is_shared' => $share->is_shared,
                'shared_at' => $share->shared_at,
                'revoked_at' => $share->revoked_at
            ];
        }
        
        return $shares;
    }
}
