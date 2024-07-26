<?php

namespace Modules\Ministry\Http\Controllers;

use App\Events\ClaimSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClaimEditRequest;
use App\Http\Requests\ClaimStoreRequest;
use App\Models\Claim;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Response;

class ClaimController extends Controller
{
    public function fetchClaims(Request $request, $guid = null)
    {
        if(!is_null($guid)){
            $claim = Claim::where('guid', $guid)->with('institution', 'program', 'student', 'allocation')->first();
            if(!is_null($claim)){
                $programs = Program::where('institution_guid', $claim->institution_guid)->IsActive()->get();
            }

            return Response::json(['status' => true, 'programs' => $programs, 'claim' => $claim]);
        }

        $body = $this->paginateClaims($request->input('in'));

        return Response::json(['status' => true, 'body' => $body]);
    }

    public function fetchStudentsClaims(Request $request, ?Claim $claim = null)
    {
        $body = $this->paginateStudentClaims($request->input('in'));

        return Response::json(['status' => true, 'body' => $body]);
    }

    public function fetchClaimsByCourse(Request $request, ?Claim $claim = null)
    {
        $body = $this->paginateClaims($request->input('in'));

        return Response::json(['status' => true, 'body' => $body]);
    }

    public function fetchClaimsByStudent(Request $request, ?Claim $claim = null)
    {
        $body = $this->paginateClaimsByStudent($request->input('in'));

        return Response::json(['status' => true, 'body' => $body]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClaimStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $claim = Claim::create($request->validated());

        $claim = Claim::find($claim->id);
        return Redirect::route('ministry.institutions.show', [$claim->institution->id, 'claims-by-course']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $claim = Claim::find($request->id);
        $claim_id = Claim::where('id', $request->id)->update($request->validated());
        event(new ClaimSubmitted($claim, $request->claim_status));

        $claim = Claim::find($request->id);
        $id = $request->page === 'students' ? $claim->student->id : $claim->institution->id;

        return Redirect::route('ministry.' . $request->page . '.show', [$id, $request->subpage]);
    }


    private function paginateClaims($institutionGuid)
    {
        $claims = Claim::where('institution_guid', $institutionGuid)->with('student', 'program', 'allocation');


        if (request()->sort !== null) {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } else {
            $claims = $claims->orderBy('first_name');
        }

        return $claims->paginate(25)->onEachSide(1)->appends(request()->query());
    }



    private function paginateStudentClaims($studentGuid)
    {
        $claims = Claim::where('student_guid', $studentGuid)->with('student', 'program', 'allocation', 'institution');

        if (request()->sort !== null) {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } else {
            $claims = $claims->orderBy('first_name');
        }

        return $claims->paginate(25)->onEachSide(1)->appends(request()->query());
    }


    private function paginateClaimsByStudent($institutionGuid)
    {
        $claims = Claim::where('institution_guid', $institutionGuid)->with('student')->pluck('student_guid');
        $students = Student::whereIn('guid', $claims)->with('claims');

        if (request()->sort !== null) {
            $students = $students->orderBy(request()->sort, request()->direction);
        } else {
            $students = $students->orderBy('first_name');
        }

        return $students->paginate(25)->onEachSide(1)->appends(request()->query());
    }
}
