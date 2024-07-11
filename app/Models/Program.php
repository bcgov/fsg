<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["guid", "institution_guid", "program_name", "program_type", "program_number", "delivery_method", "online_delivery_type",
"credential_type", "micro_credential_type", "high_priority_industry", "total_duration_hrs", "creditable", "full_time", "prov_funded_micro_cred",
"indigenous_related_learning", "diversity_inclusion_related_learning", "active_status", "excel_guid",
"start_date", "end_date", "last_touch_by_user_guid", ];

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


    /**
     * Scope a query to only include admin users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsActive($query)
    {
        return $query->where('active_status',  true);
    }
}
