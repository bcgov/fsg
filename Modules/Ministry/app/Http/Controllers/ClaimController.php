<?php

namespace Modules\Ministry\Http\Controllers;

use App\Events\ClaimSubmitted;
use App\Events\MinistryClaimSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClaimEditRequest;
use App\Http\Requests\MinistryClaimEditRequest;
use App\Http\Requests\ClaimStoreRequest;
use App\Models\Allocation;
use App\Models\Claim;
use App\Models\Program;
use App\Models\ProgramYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Response;

class ClaimController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $claims = $this->paginateClaims();

        return Inertia::render('Ministry::Claims', ['error' => null, 'results' => $claims,
            'countries' => null,]);
    }

    public function fetchClaims(Request $request, $guid = null)
    {
        if (! is_null($guid)) {
            $claim = Claim::where('guid', $guid)->with('institution', 'program', 'student', 'allocation')->first();
            if (! is_null($claim)) {
                $programs = Program::where('institution_guid', $claim->institution_guid)->get();
            }

            return Response::json(['status' => true, 'programs' => $programs, 'claim' => $claim]);
        }

        if($request->has('in')) {
            $body = $this->paginateClaimsByInstitution($request->input('in'));
        }else{
            $body = $this->paginateClaims();
        }

        return Response::json(['status' => true, 'body' => $body]);
    }

    public function fetchStudentsClaims(Request $request, ?Claim $claim = null)
    {
        $body = $this->paginateStudentClaims($request->input('in'));

        return Response::json(['status' => true, 'body' => $body]);
    }

    public function fetchClaimsByCourse(Request $request, ?Claim $claim = null)
    {
        $body = $this->paginateClaimsByInstitution($request->input('in'));

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

    public function clearClaimOutcome(Request $request)
    {
        $claim = Claim::find($request->id);
        $claim->outcome_status = null;
        $claim->outcome_effective_date = null;
        $claim->save();

        return Redirect::route('ministry.institutions.show', [$claim->institution->id, 'claims-by-course']);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MinistryClaimEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $oldClaim = Claim::find($request->id);
        $claim = Claim::find($request->id);
        $claim->fill($request->validated());
        $claim->save();

//        $claim_id = Claim::where('id', $request->id)->update($request->validated());
        event(new MinistryClaimSubmitted($oldClaim, $request->claim_status));

        $claim = Claim::find($request->id);
        $id = $request->page === 'students' ? $claim->student->id : $claim->institution->id;

        return Redirect::route('ministry.'.$request->page.'.index');
    }

    private function paginateClaimsByInstitution($institutionGuid)
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


    private function paginateClaims()
    {
        $claims = Claim::whereNotIn('claim_status', ['Draft'])
            ->with('student', 'program');

        if (request()->filter_term !== null && request()->filter_type !== null) {
            if(request()->filter_type === 'status'){

                // Searching by status should be limited to the statuses visible to the institutions
                $claim_status = match (Str::lower(request()->filter_term)) {
                    'submitted' => 'Submitted',
                    'hold' => 'Hold',
                    'claimed' => 'Claimed',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
                };
            }

            $claims = match (request()->filter_type) {
//                'program' => $claims->where('program_guid', request()->filter_term),
                'fname' => $claims->where('first_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'lname' => $claims->where('last_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'sin' => $claims->where('sin', 'ILIKE', '%'.request()->filter_term.'%'),
                'email' => $claims->where('email', 'ILIKE', '%'.request()->filter_term.'%'),
                'status' => $claims->where('claim_status', 'ILIKE', $claim_status),
                'py_start_date' => $claims->join('allocations', 'claims.allocation_guid', '=', 'allocations.guid')
                    ->join('program_years', 'allocations.program_year_guid', '=', 'program_years.guid')
                    ->where('program_years.start_date', request()->filter_term)
                    ->select('claims.*'),
                'program' => $claims->join('programs', 'claims.program_guid', '=', 'programs.guid')
                    ->where('programs.program_name', request()->filter_term)
                    ->select('claims.*'),
                'institution' => $claims->join('institutions', 'claims.institution_guid', '=', 'institutions.guid')
                    ->where('institutions.name', request()->filter_term)
                    ->select('claims.*'),

        default => $claims, // Default case: return $claims unchanged
            };
        }

        if (request()->sort !== null && request()->sort !== 'py' && request()->sort !== 'institution' && request()->sort !== 'program') {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } elseif (request()->sort === 'py') {
            // Join the allocations table to sort on its start_date column.
            $claims = $claims->join('allocations', 'claims.allocation_guid', '=', 'allocations.guid')
                ->join('program_years', 'allocations.program_year_guid', '=', 'program_years.guid')
                ->orderBy('program_years.start_date', request()->direction)
                ->select('claims.*');
        }  elseif (request()->sort === 'institution') {
            $claims = $claims->join('institutions', 'claims.institution_guid', '=', 'institutions.guid')
                ->orderBy('institutions.name', request()->direction)
                ->select('claims.*');  // Ensure only Claim columns are returned
        }  elseif (request()->sort === 'program') {
            $claims = $claims->join('programs', 'claims.program_guid', '=', 'programs.guid')
                ->orderBy('programs.program_name', request()->direction)
                ->select('claims.*');  // Ensure only Claim columns are returned
        } else {
            $claims = $claims->orderBy('created_at', 'desc');
        }

        return $claims->with('institution.allocations', 'institution.programs')->paginate(25)->onEachSide(1)->appends(request()->query());
    }

    private function getAllocations()
    {
        $programYear = ProgramYear::active()->first();

        return Allocation::where('program_year_guid', $programYear->guid)
            ->with('py')->orderByDesc('created_at')->get();
    }
}
