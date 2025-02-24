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
}
