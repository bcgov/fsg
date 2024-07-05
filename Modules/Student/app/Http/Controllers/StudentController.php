<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentEditRequest;
use App\Http\Requests\StudentStoreRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index($page = 'profile')
    {
        $student = Student::where('user_guid', Auth::user()->guid)->first();

        return Inertia::render('Student::Dashboard', ['status' => true, 'results' => $student, 'page' => $page]);
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
    public function store(StudentStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $student_id = Student::create($request->validated());
        $student = Student::find($request->id);
        return Redirect::route('student.home');
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
