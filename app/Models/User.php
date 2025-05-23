<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['guid', 'name', 'first_name', 'last_name', 'disabled', 'email', 'password',
        'idir_user_guid', 'bceid_user_guid', 'bceid_business_guid', 'bceid_username', 'idir_username', ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password'];

    protected $casts = ['disabled' => 'boolean',];

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function institutionStaff()
    {
        return $this->belongsTo(InstitutionStaff::class, 'guid', 'user_guid');
    }

    public function getInstitutionAttribute()
    {
        if (is_null($this->institutionStaff)) {
            return null;
        }

        return $this->institutionStaff->institution;
    }

    /**
     * Check if the user has an active institution.
     */
    public function hasActiveInstitution()
    {
        if (is_null($this->institution)) {
            return false;
        }

        return $this->institution->active_status === true;
    }

    /**
     * Check if the user has an institution with Info Sharing agreement TRUE
     */
    public function hasActiveIsa()
    {
        if (is_null($this->institution)) {
            return false;
        }

        return $this->institution->info_sharing_agreement === true;
    }

    /**
     * The roles that belong to the user.
     */
    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }

    /**
     * Scope a query to only include admin users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsActive($query)
    {
        return $query->where('disabled', '=', false);
    }
}
