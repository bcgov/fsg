<?php

namespace App\Rules;

use App\Models\Demographic;
use App\Models\Student;
use App\Models\StudentDemographic;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class RequiredDemographicsCompleted implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get the student from the authenticated user
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        if (!$student) {
            $fail('Student not found.');
            return;
        }

        // Get all required and active demographics
        $requiredDemographics = Demographic::where('required', true)
            ->where('active', true)
            ->get();

        if ($requiredDemographics->isEmpty()) {
            // No required demographics, validation passes
            return;
        }

        // Get student's answered demographics
        $answeredDemographicIds = StudentDemographic::where('student_guid', $student->guid)
            ->whereHas('answers') // Only demographics with actual answers
            ->pluck('demographic_id')
            ->toArray();

        // Check if all required demographics have been answered
        $unansweredRequired = $requiredDemographics->filter(function ($demographic) use ($answeredDemographicIds) {
            return !in_array($demographic->id, $answeredDemographicIds);
        });

        if ($unansweredRequired->isNotEmpty()) {
            $profileUrl = route('student.profile');
            $fail("You must complete all required demographic questions in your profile before submitting an application. Please visit your <a href='{$profileUrl}' target='_blank'>profile page</a> to complete the missing information.");
        }
    }
}
