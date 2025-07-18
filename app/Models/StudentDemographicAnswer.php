<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudentDemographicAnswer extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['student_demographic_id', 'value', 'label_snapshot'];

    /**
     * Get the student demographic that owns this answer.
     */
    public function studentDemographic()
    {
        return $this->belongsTo(StudentDemographic::class);
    }

    /**
     * Get the demographic option (if selected).
     */
    public function demographicOption()
    {
        return $this->belongsTo(DemographicOption::class);
    }

    /**
     * Get the student through the student demographic.
     */
    public function student()
    {
        return $this->hasOneThrough(
            Student::class,
            StudentDemographic::class,
            'id',
            'guid',
            'student_demographic_id',
            'student_guid'
        );
    }

    /**
     * Get the demographic question through the student demographic.
     */
    public function demographic()
    {
        return $this->hasOneThrough(
            Demographic::class,
            StudentDemographic::class,
            'id',
            'id',
            'student_demographic_id',
            'demographic_id'
        );
    }
}
