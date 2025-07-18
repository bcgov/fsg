<?php

namespace Modules\Student\Http\Controllers;

use App\Events\ApplicationSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationEditRequest;
use App\Http\Requests\ApplicationStoreRequest;
use App\Models\Claim;
use App\Models\Demographic;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentDemographic;
use App\Models\StudentDemographicAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Response;

class ApplicationController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(ApplicationEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = collect($request->validated())->except(['allocation_limit_reached', 'demographics'])->toArray();

        $claim = Claim::find($request->id);
        $claim->fill($validated);
        $claim->save();

        // Handle demographics data
        if ($request->has('demographics')) {
            $this->saveDemographics($claim->student_guid, $request->demographics);
        }

        $application = Claim::find($request->id);
        event(new ApplicationSubmitted($application, $request->claim_status));

        return Redirect::route('student.home');
    }

    /**
     * Store the specified resource in storage.
     */
    public function store(ApplicationStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = collect($request->validated())->except(['allocation_limit_reached', 'demographics'])->toArray();

        $application = Claim::create($validated);
        
        // Handle demographics data
        if ($request->has('demographics')) {
            $this->saveDemographics($application->student_guid, $request->demographics);
        }
        
        event(new ApplicationSubmitted($application, $request->claim_status));

        return Redirect::route('student.home');
    }

    public function applications(Request $request, $page = 'applications')
    {
        $providerUser = null;
        $student = Student::with('applications')->where('user_guid', Auth::user()->guid)->first();
        if (is_null($student)) {
            $page = 'profile';
            $providerUser = json_decode(Cache::get('bcsc_provider_user_' . Auth::user()->id));
        }

        return Inertia::render('Student::Dashboard', ['status' => true, 'results' => $student,
            'page' => $page, 'providerUser' => $providerUser]);
    }

    public function fetchApplications(Request $request)
    {
        $body = $this->paginateClaims();

        return Response::json(['status' => true, 'body' => $body]);
    }

    public function fetchInstitutions(Request $request, $institution = null)
    {
        if (! is_null($institution)) {
            $institution = Institution::where('guid', $institution)->with('activePrograms')->first();

            return Response::json(['status' => true, 'institution' => $institution]);
        }

        $institutions = Institution::active()->get();

        return Response::json(['status' => true, 'institutions' => $institutions]);
    }

    private function paginateClaims()
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        if (is_null($student)) {
            return null;
        }

        $claims = Claim::where('student_guid', $student->guid)->with('student', 'program', 'institution');

        if (request()->sort !== null) {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } else {
            $claims = $claims->orderBy('created_at', 'desc');
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

    /**
     * Save demographics data for a student
     */
    private function saveDemographics($studentGuid, $demographicsData)
    {
        if (!$demographicsData || !is_array($demographicsData)) {
            return;
        }

        foreach ($demographicsData as $demographicId => $answer) {
            if (empty($answer)) {
                continue;
            }

            // Get the demographic to create snapshot data
            $demographic = Demographic::find($demographicId);
            if (!$demographic) {
                continue;
            }

            // Find or create student demographic record
            $studentDemographic = StudentDemographic::updateOrCreate(
                [
                    'student_guid' => $studentGuid,
                    'demographic_id' => $demographicId
                ],
                [
                    'question_snapshot' => $demographic->question,
                    'type' => $demographic->type,
                    'answered_at' => now()
                ]
            );

            // Clear existing answers for this student demographic
            StudentDemographicAnswer::where('student_demographic_id', $studentDemographic->id)->delete();

            // Handle different answer types
            if (is_array($answer)) {
                // Multi-select or checkbox answers
                foreach ($answer as $value) {
                    if (!empty($value)) {
                        StudentDemographicAnswer::create([
                            'student_demographic_id' => $studentDemographic->id,
                            'value' => $value,
                            'label_snapshot' => $this->getOptionLabel($demographic, $value)
                        ]);
                    }
                }
            } else {
                // Single answer (text, select, radio)
                if (strpos($answer, ',') !== false) {
                    // Comma-separated values (checkbox answers stored as string)
                    $values = explode(',', $answer);
                    foreach ($values as $value) {
                        $value = trim($value);
                        if (!empty($value)) {
                            StudentDemographicAnswer::create([
                                'student_demographic_id' => $studentDemographic->id,
                                'value' => $value,
                                'label_snapshot' => $this->getOptionLabel($demographic, $value)
                            ]);
                        }
                    }
                } else {
                    // Single value
                    StudentDemographicAnswer::create([
                        'student_demographic_id' => $studentDemographic->id,
                        'value' => $answer,
                        'label_snapshot' => $this->getOptionLabel($demographic, $answer)
                    ]);
                }
            }
        }
    }

    /**
     * Get the label for a demographic option value
     */
    private function getOptionLabel($demographic, $value)
    {
        $option = $demographic->options->where('value', $value)->first() ?: 
                 $demographic->options->where('label', $value)->first();
        
        return $option ? $option->label : $value;
    }
}
