<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicationStoreRequest;
use App\Http\Requests\StudentEditRequest;
use App\Http\Requests\StudentStoreRequest;
use App\Models\Claim;
use App\Models\Institution;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Response;

class ApplicationController extends Controller
{

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
    public function store(ApplicationStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        // Log the incoming payload
//        \Log::info('Incoming Payload:', $request->all());
//
//        // Log the validated data
//        \Log::info('Validated Data:', $validated);

        $application = Claim::create($validated);
        event(new ApplicationSubmitted($application, $request->claim_status));


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
        if(!is_null($institution)) {
            $institution = Institution::where('guid', $institution)->with('activePrograms')->first();
            return Response::json(['status' => true, 'institution' => $institution]);
        }

        $institutions = Institution::active()->get();
        return Response::json(['status' => true, 'institutions' => $institutions]);
    }



    private function paginateClaims()
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();
        if(is_null($student)){
           return null;
        }

        $claims = Claim::where('student_guid', $student->guid)->with('student', 'program', 'institution');
//
//        if (request()->filter_name !== null) {
//            $institutions = $institutions->where('name', 'ILIKE', '%'.request()->filter_name.'%');
//        }

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
}
