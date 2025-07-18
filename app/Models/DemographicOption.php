<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DemographicOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['demographic_id', 'label', 'value', 'order'];
    protected $casts = ['order' => 'integer',];

    /**
     * Get the demographic that owns this option.
     */
    public function demographic()
    {
        return $this->belongsTo(Demographic::class);
    }

    /**
     * Get the student demographic answers for this option.
     */
    public function studentDemographicAnswers()
    {
        return $this->hasMany(StudentDemographicAnswer::class);
    }

}
