<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["guid", "user_guid", "sin", "first_name", "last_name", "dob", "email", "city", "zip_code", "total_grant",
        'citizenship', 'grade12_or_over19', 'info_consent', 'duplicative_funding', 'tax_implications', 'lifetime_max',
        'fed_prov_benefits' , 'workbc_client', 'additional_supports', 'bc_resident',];

    public function claims()
    {
        return $this->hasMany(Claim::class, 'student_guid', 'guid')->orderBy('created_at');
    }
}
