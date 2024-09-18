<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentEditRequest;
use App\Http\Requests\StudentStoreRequest;
use App\Models\Claim;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Response;

class StudentController extends Controller
{
    public function index($page = 'profile', $error = null)
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();

        return Inertia::render('Student::Dashboard', ['status' => true, 'results' => $student, 'page' => $page, 'error' => $error]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $student_id = Student::where('id', $request->id)->update($request->validated());
        $student = Student::find($request->id);

        return Redirect::route('student.home');
    }

    /**
     * Update the specified resource in storage.
     */
    public function store(StudentStoreRequest $request): \Illuminate\Http\RedirectResponse|\Inertia\Response
    {
        // Check if a student with the given SIN already exists
        $existingStudent = Student::where('sin', $request->sin)->first();

        if ($existingStudent) {
            // If dob matches as well, update the existing student record
            if ($request->dob === $existingStudent->dob) {

                // Validate the request
                $validatedData = $request->validated();

                // Remove 'guid' from validated data
                unset($validatedData['guid']);

                // Update the existing student record with all request data plus user_guid
                $existingStudent->update(array_merge(
                    $validatedData, // Merge validated request data
                    ['user_guid' => Auth::user()->guid] // Add user_guid
                ));

            } else {
                return $this->index('profile', 'Failed to connect account');
            }
        } else {
            // Create a new student record
            $student_id = Student::create($request->validated());
        }
        $student = Student::find($request->id);

        return Redirect::route('student.home');
    }

    public function applications($page = 'applications')
    {
        $student = Student::with('applications')->where('user_guid', Auth::user()->guid)->first();

        return Inertia::render('Student::Dashboard', ['status' => true, 'results' => $student, 'page' => $page]);
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
}
