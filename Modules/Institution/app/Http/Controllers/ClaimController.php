<?php

namespace Modules\Institution\Http\Controllers;

use App\Events\ClaimSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClaimEditRequest;
use App\Models\Allocation;
use App\Models\Claim;
use App\Models\Country;
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
        // Get the inst cap and check if we have hit the cap for issued attestations
        // This is going to be all attes. under this inst. and are using the same fed cap as this.
        $user = User::find(Auth::user()->id);
        $allocations = $this->getAllocations($user);
//        $allocation = $allocations->where('institution_guid', $user->institution->guid)->first();

        $claims = $this->paginateClaims($allocations);
        \Log::info("AllocationGuids 0.1: " . json_encode($allocations->pluck('guid')->toArray()));

        return Inertia::render('Institution::Claims', ['error' => null, 'results' => $claims,
            'institution' => $user->institution,
            'countries' => null,
//            'allocation' => $allocation
        ]);
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

        $user = User::find(Auth::user()->id);
        $allocations = $this->getAllocations($user);
        \Log::info("AllocationGuids 0: " . json_encode($allocations->pluck('guid')->toArray()));

        //$allocation = $allocations->where('institution_guid', $user->institution->guid)->first();
        $body = $this->paginateClaims($allocations);

        return Response::json(['status' => true, 'body' => $body]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClaimEditRequest $request): \Illuminate\Http\RedirectResponse | \Inertia\Response
    {
        $oldClaim = Claim::find($request->id);
        $claim = Claim::find($request->id);
        $claim->fill($request->validated());
        $claim->save();

        event(new ClaimSubmitted($oldClaim, $request->claim_status));

        $claim = Claim::find($request->id);
        $id = $request->page === 'students' ? $claim->student->id : $claim->institution->id;

        if(isset($request->page) && $request->page === 'students') {
            $student = Student::where('id', $claim->student->id)->with(
                ['claims']
            )->first();

            $countries = Cache::remember('countries', 380, function () {
                return Country::where('active', true)->orderBy('name')->get();
            });
            $program_years = Cache::remember('program_years', 380, function () {
                return ProgramYear::where('status', 'active')->orderBy('guid')->get();
            });
            return Inertia::render('Institution::Student', ['page' => 'claims', 'results' => $student,
                'countries' => $countries, 'programYears' => $program_years]);
        }
        return Redirect::route('institution.claims.index', request()->query());
    }

    public function fetchStudentsClaims(Request $request, ?Claim $claim = null)
    {
        $body = $this->paginateStudentClaims($request->input('in'));

        return Response::json(['status' => true, 'body' => $body]);
    }

    private function paginateStudentClaims($studentGuid)
    {
        $user = Auth::user();
        $claims = Claim::where('student_guid', $studentGuid)->with('student', 'program', 'allocation', 'institution')
            ->where('institution_guid', $user->institution->guid);

        if (request()->sort !== null) {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } else {
            $claims = $claims->orderBy('first_name');
        }

        return $claims->paginate(25)->onEachSide(1)->appends(request()->query());
    }

    public function exportCsv()
    {

        $user = User::find(Auth::user()->id);
        $allocations = $this->getAllocations($user);

        $allocation = $allocations->where('institution_guid', $user->institution->guid)->first();

        $data = Claim::where('institution_guid', $user->institution->guid)
            ->where('allocation_guid', $allocation->guid)
            ->whereNotIn('claim_status', ['Draft'])
            ->with('student', 'program', 'allocation', 'institution')
            ->orderByDesc('created_at')->get();

        $csvData = [];
        $csvDataHeader = ['PROGRAM NAME', 'SIN', 'FIRST NAME', 'LAST NAME', 'DOB', 'EMAIL', 'CITY',
            'POSTAL CODE', 'STATUS', 'REGISTRATION FEE', 'MATERIALS FEE', 'PROGRAM FEE', 'ADMIN %', 'FUNDING TYPE',
            'EST. HOLD AMOUNT', 'TOTAL CLAIM AMOUNT', 'STABLE ENROL. DATE', 'EXPEC. STABLE ENROL. DATE', 'EXPIRY DATE','CLAIMED BY', 'ISSUE DATE', 'FEEDBACK',
            'OUTCOME EFFECT. DATE', 'OUTCOME STATUS'];

        foreach ($data as $d) {
            $csvData[] = [$d->program->program_name, $d->sin, $d->first_name, $d->last_name, $d->dob, $d->email, $d->city,
                $d->zip_code, $d->claim_status, $d->registration_fee, $d->materials_fee, $d->program_fee, $d->claim_percent, $d->program->funding_type,
                $d->estimated_hold_amount, $d->total_claim_amount, $d->stable_enrolment_date, $d->expected_stable_enrolment_date,
                $d->expiry_date, $d->claimed_by_name, $d->updated_at, $d->process_feedback, $d->outcome_effective_date, $d->outcome_status];
        }
        $output = fopen('php://temp', 'w');
        // Write CSV headers
        fputcsv($output, $csvDataHeader);

        // Write CSV rows
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $response = Response::make(stream_get_contents($output), 200);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-Disposition', 'attachment; filename="claims_export.csv"');
        fclose($output);

        return $response;
    }

    private function paginateClaims($allocations)
    {
        $user = Auth::user();

        if (empty($allocations)) {
            // Return empty paginator
            $emptyData = new Collection();
            $emptyPaginator = new LengthAwarePaginator(
                $emptyData, // Items
                0, // Total items
                25, // Items per page
                1, // Current page
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'query' => request()->query(),
                ]
            );

            return $emptyPaginator->onEachSide(1);
        }

        // An institution can have multiple allocations for the same program year
        // So we need to get the allocation guids
        $allocationGuids = $allocations->pluck('guid')->toArray();
        \Log::info("AllocationGuids: " . json_encode($allocationGuids));

        $claims = Claim::where('institution_guid', $user->institution->guid)
            ->whereIn('allocation_guid', $allocationGuids)
//            ->where('allocation_guid', $allocation->guid)
            ->whereNotIn('claim_status', ['Draft'])
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
                'program' => $claims->where('program_guid', request()->filter_term),
                'fname' => $claims->where('first_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'lname' => $claims->where('last_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'sin' => $claims->where('sin', 'ILIKE', '%'.request()->filter_term.'%'),
                'email' => $claims->where('email', 'ILIKE', '%'.request()->filter_term.'%'),
                'status' => $claims->where('claim_status', 'ILIKE', $claim_status),
                default => $claims, // Default case: return $claims unchanged
            };
        }

        if (request()->sort !== null) {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } else {
            $claims = $claims->orderBy('created_at', 'desc');
        }

        return $claims->with('institution.allocations', 'institution.programs')->paginate(25)->onEachSide(1)->appends(request()->query());
    }

    private function getAllocations($user)
    {
        $cacheProgramYear = Cache::get('global_program_years_' . $user->institution->guid);
        $programYear = ProgramYear::where('guid', $cacheProgramYear['default'])->first();

        $allocations = Allocation::where('institution_guid', $user->institution->guid)
            ->where('program_year_guid', $programYear->guid)
            ->with('py')->orderByDesc('created_at')->get();

        \Log::info("programYear guid: " . $cacheProgramYear['default']);
        \Log::info("AllocationGuids 1: " . json_encode($allocations->pluck('guid')->toArray()));

        return $allocations;
    }
}
