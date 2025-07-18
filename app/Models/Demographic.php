<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Demographic extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['question', 'description', 'type', 'required', 'active', 'order'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'required' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Get the demographic options for this demographic.
     */
    public function options()
    {
        return $this->hasMany(DemographicOption::class)->orderBy('order');
    }

    /**
     * Get the student demographics for this demographic.
     */
    public function studentDemographics()
    {
        return $this->hasMany(StudentDemographic::class);
    }

    /**
     * Scope a query to only include active demographics.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to order demographics by their order field.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('question');
    }
}
