<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentEditRequest;
use App\Http\Requests\StudentStoreRequest;
use App\Models\Claim;
use App\Models\Demographic;
use App\Models\StudentDemographicAnswer;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentDemographic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Response;

class StudentController extends Controller
{
    public function index($page = 'profile', $error = null)
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        
        // Load active demographics with their options
        $demographics = Demographic::with('options')
            ->active()
            ->ordered()
            ->get();

        // Get existing demographic answers for this student
        $existingDemographics = $student ? $student->getFormattedDemographics() : [];

        return Inertia::render('Student::Dashboard', [
            'status' => true, 
            'results' => $student, 
            'page' => $page, 
            'error' => $error,
            'demographics' => $demographics,
            'existingDemographics' => $existingDemographics
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

        return Redirect::route('student.home');
    }

    /**
     * Update the specified resource in storage.
     */
    public function store(StudentStoreRequest $request): \Illuminate\Http\RedirectResponse|\Inertia\Response
    {
        $validated = $request->validated();
        $demographics = $validated['demographics'] ?? [];
        unset($validated['demographics']);
        
        // Check if a student with the given SIN already exists
        $existingStudent = Student::where('sin', $request->sin)->first();

        if ($existingStudent) {
            // If dob matches as well, update the existing student record
            if ($request->dob === $existingStudent->dob) {

                // Remove 'guid' from validated data
                unset($validated['guid']);

                // Update the existing student record with all request data plus user_guid
                $existingStudent->update(array_merge(
                    $validated, // Merge validated request data
                    ['user_guid' => Auth::user()->guid] // Add user_guid
                ));
                
                $student = $existingStudent;

            } else {
                return $this->index('profile', 'Failed to connect account');
            }
        } else {
            // Create a new student record
            $student = Student::create($validated);
        }
        
        // Handle demographics saving
        if (!empty($demographics) && $student) {
            $this->saveDemographics($student, $demographics);
        }

        return Redirect::route('student.home');
    }

    public function applications($page = 'applications')
    {
        $student = Student::with('applications')->where('user_guid', Auth::user()->guid)->first();
        
        // Load active demographics with their options
        $demographics = Demographic::with('options')
            ->active()
            ->ordered()
            ->get();

        // Get existing demographic answers for this student
        $existingDemographics = $student ? $student->getFormattedDemographics() : [];

        return Inertia::render('Student::Dashboard', [
            'status' => true, 
            'results' => $student, 
            'page' => $page,
            'demographics' => $demographics,
            'existingDemographics' => $existingDemographics
        ]);
    }

    public function fetchApplications(Request $request)
    {
        $body = $this->paginateClaims();

        return Response::json(['status' => true, 'body' => $body]);
    }

    public function fetchInstitutions(Request $request, $institution = null)
    {
        if (! is_null($institution)) {
            $institution = Institution::where('guid', $institution)->with('activePrograms')
                ->whereHas('allocations', function ($query) {
                    $query->where('status', 'active');
                })->first();

            return Response::json(['status' => true, 'institution' => $institution]);
        }

        $institutions = Institution::active()->whereHas('allocations', function ($query) {
            $query->where('status', 'active');
        })->get();

        return Response::json(['status' => true, 'institutions' => $institutions]);
    }

    private function paginateClaims()
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        if (is_null($student)) {
            return null;
        }

        $claims = Claim::where('student_guid', $student->guid)->with('student', 'program', 'allocation');

        if (request()->sort !== null) {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } else {
            $claims = $claims->orderBy('first_name');
        }

        return $claims->paginate(25)->onEachSide(1)->appends(request()->query());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('student::create');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('student::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('student::edit');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }


    public function faqList(Request $request): \Inertia\Response
    {
        $faqs = Faq::where('active_status', true)->orderBy('order', 'asc')->get();

        return Inertia::render('Student::Faq', ['status' => true, 'results' => $faqs,]);
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
