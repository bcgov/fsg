<?php

namespace Modules\Ministry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentEditRequest;
use App\Models\Country;
use App\Models\ProgramYear;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Models\Demographic;
use App\Models\StudentDemographic;
use App\Models\StudentDemographicAnswer;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = $this->paginateStudents();

        return Inertia::render('Ministry::Students', ['status' => true, 'results' => $students]);
    }

    /**
     * Show the specified resource.
     */
    public function show(Student $student, $page = 'details')
    {
        $student = Student::where('id', $student->id)->with(
            ['claims']
        )->first();

        $countries = Cache::remember('countries', 380, function () {
            return Country::where('active', true)->orderBy('name')->get();
        });
        $program_years = Cache::remember('program_years_ministry', 380, function () {
            return ProgramYear::orderBy('guid')->get();
        });

        
        // Load active demographics with their options
        $demographics = Demographic::with('options')
            ->active()
            ->ordered()
            ->get();

        // Get existing demographic answers for this student
        $existingDemographics = $student ? $student->getFormattedDemographics() : [];

        // return Inertia::render('Student::Dashboard', [
        //     'status' => true, 
        //     'results' => $student, 
        //     'page' => $page, 
        //     'error' => $error,
        //     'demographics' => $demographics,
        //     'existingDemographics' => $existingDemographics
        // ]);

        return Inertia::render('Ministry::Student', [
            'page' => $page, 
            'results' => $student,
            'countries' => $countries, 
            'programYears' => $program_years,
            'demographics' => $demographics,
            'existingDemographics' => $existingDemographics,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();
        $demographics = $validated['demographics'] ?? [];
        unset($validated['demographics']);

        $student_id = Student::where('id', $request->id)->update($validated);
        $student = Student::find($request->id);

        // Handle demographics saving
        if (!empty($demographics) && $student) {
            $this->saveDemographics($student, $demographics);
        }

        return Redirect::route('ministry.students.show', [$student->id]);
    }

    private function paginateStudents()
    {
        $students = Student::with('claims');

//        if (request()->filter_last_name !== null) {
//            $students = $students->where('last_name', 'ILIKE', '%'.request()->filter_last_name.'%');
//        }
//        if (request()->filter_email !== null) {
//            $students = $students->where('email', 'ILIKE', '%'.request()->filter_email.'%');
//        }

        if (request()->filter_term !== null && request()->filter_type !== null) {
            $students = match (request()->filter_type) {
                'fname' => $students->where('first_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'lname' => $students->where('last_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'sin' => $students->where('sin', 'ILIKE', '%'.request()->filter_term.'%'),
                'email' => $students->where('email', 'ILIKE', '%'.request()->filter_term.'%'),
                default => $students, // Default case: return $students unchanged
            };
        }

        if (request()->sort !== null) {
            $students = $students->orderBy(request()->sort, request()->direction);
        } else {
            $students = $students->orderBy('last_name');
        }

        return $students->paginate(25)->onEachSide(1)->appends(request()->query());
    }

    
    /**
     * Save demographics for a student
     */
    private function saveDemographics(Student $student, array $demographics)
    {
        DB::transaction(function () use ($student, $demographics) {
            foreach ($demographics as $demographicData) {
                if (!isset($demographicData['demographic_id']) || !isset($demographicData['answers'])) {
                    continue;
                }
                
                $demographicId = $demographicData['demographic_id'];
                $answers = $demographicData['answers'];
                
                // Get the demographic question for the snapshot
                $demographic = Demographic::find($demographicId);
                if (!$demographic) {
                    continue;
                }
                
                // Create question snapshot
                $questionSnapshot = [
                    'id' => $demographic->id,
                    'question' => $demographic->question,
                    'type' => $demographic->type,
                    'required' => $demographic->required,
                    'description' => $demographic->description,
                    'options' => $demographic->options,
                    'captured_at' => now()->toISOString()
                ];
                
                // Find or create student demographic record
                $studentDemographic = StudentDemographic::firstOrCreate([
                    'student_guid' => $student->guid,
                    'demographic_id' => $demographicId,
                ], [
                    'question_snapshot' => json_encode($questionSnapshot),
                    'type' => $demographic->type,
                    'answered_at' => now(),
                ]);
                
                // Update the record if it already existed (in case question changed)
                if ($studentDemographic->wasRecentlyCreated === false) {
                    $studentDemographic->update([
                        'question_snapshot' => json_encode($questionSnapshot),
                        'type' => $demographic->type,
                        'answered_at' => now(),
                    ]);
                }
                
                // Delete existing answers for this demographic
                StudentDemographicAnswer::where('student_demographic_id', $studentDemographic->id)->delete();
                
                // Create new answers
                foreach ($answers as $answerValue) {
                    if (!empty($answerValue)) {
                        // For select/radio/checkbox types, find the corresponding option label
                        $labelSnapshot = $answerValue; // Default to the value itself
                        
                        if (in_array($demographic->type, ['select', 'radio', 'checkbox', 'multi-select'])) {
                            // Try to find the option that matches this value
                            $matchingOption = $demographic->options->firstWhere('value', $answerValue) 
                                           ?? $demographic->options->firstWhere('label', $answerValue);
                            
                            if ($matchingOption) {
                                $labelSnapshot = $matchingOption->label;
                            }
                        }
                        
                        StudentDemographicAnswer::create([
                            'student_demographic_id' => $studentDemographic->id,
                            'value' => $answerValue,
                            'label_snapshot' => $labelSnapshot,
                        ]);
                    }
                }
            }
        });
    }
}
