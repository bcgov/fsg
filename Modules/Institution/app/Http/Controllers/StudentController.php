<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionEditRequest;
use App\Http\Requests\InstitutionStoreRequest;
use App\Http\Requests\StudentEditRequest;
use App\Models\Country;
use App\Models\Student;
use App\Models\ProgramYear;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Response;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = $this->paginateStudents();

        return Inertia::render('Institution::Students', ['status' => true, 'results' => $students]);
    }

    /**
     * Show the specified resource.
     */
    public function show(Student $student, $page = 'claims')
    {
        $student = Student::where('id', $student->id)->with(
            ['claims',]
        )->first();

        $countries = Cache::remember('countries', 380, function () {
            return Country::where('active', true)->orderBy('name')->get();
        });
        $program_years = Cache::remember('program_years', 380, function () {
            return ProgramYear::where('status', 'active')->orderBy('guid')->get();
        });

        return Inertia::render('Institution::Student', ['page' => $page, 'results' => $student,
            'countries' => $countries, 'programYears' => $program_years]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(StudentEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $student_id = Student::where('id', $request->id)->update($request->validated());
        $student = Student::find($request->id);
        return Redirect::route('institution.students.show', [$student->id]);
    }

    private function paginateStudents()
    {
        $students = Student::with('claims');

        if (request()->filter_last_name !== null) {
            $students = $students->where('last_name', 'ILIKE', '%'.request()->filter_last_name.'%');
        }
        if (request()->filter_email !== null) {
            $students = $students->where('email', 'ILIKE', '%'.request()->filter_email.'%');
        }

        if (request()->sort !== null) {
            $students = $students->orderBy(request()->sort, request()->direction);
        } else {
            $students = $students->orderBy('last_name');
        }

        return $students->paginate(25)->onEachSide(1)->appends(request()->query());
    }
}
