<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudentDemographic extends Model
{

    protected $fillable = ['student_guid', 'demographic_id', 'question_snapshot', 'type', 'answered_at'];


    /**
     * Get the student that owns this demographic.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_guid', 'guid');
    }

    /**
     * Get the demographic question.
     */
    public function demographic()
    {
        return $this->belongsTo(Demographic::class);
    }

    /**
     * Get the answers for this student demographic.
     */
    public function answers()
    {
        return $this->hasMany(StudentDemographicAnswer::class);
    }

    /**
     * Get the first answer for this student demographic.
     */
    public function answer()
    {
        return $this->hasOne(StudentDemographicAnswer::class);
    }
}
