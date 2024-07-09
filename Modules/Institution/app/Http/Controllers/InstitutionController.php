<?php

namespace Modules\Institution\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use App\Models\Claim;
use App\Models\ProgramYear;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class InstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::find(Auth::user()->id);
        $institution = $user->institution;

        // Eager load active allocation
        $institution->load(['allocations' => function ($query) {
            $query->where('status', 'active');
        }]);

        $instActiveAllocation = $institution->allocations->first();


        $cacheProgramYear = Cache::get('global_program_years', function () {
            $programYears = ProgramYear::orderBy('id')->get();
            $programYear = ProgramYear::where('status', 'active')->first();
            return [
                'list' => $programYears,
                'default' => $programYear->guid
            ];
        });

        $programYear = ProgramYear::where('guid', $cacheProgramYear['default'])->first();

        if(!is_null($instActiveAllocation)){
            // Combine claim counts into a single query
            $claimCounts = Claim::where('institution_guid', $institution->guid)
                ->where('allocation_guid', $instActiveAllocation->guid)
                ->selectRaw("
            SUM(CASE WHEN claim_status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN claim_status = 'Hold' THEN 1 ELSE 0 END) as hold,
            SUM(CASE WHEN claim_status = 'Claimed' AND reporting_completed_date IS NULL THEN 1 ELSE 0 END) as claimed,
            SUM(CASE WHEN claim_status = 'Claimed' AND reporting_completed_date IS NOT NULL THEN 1 ELSE 0 END) as reportingcomplete
        ")
                ->first();
        }


        return Inertia::render('Institution::Dashboard', [
            'results' => $institution,
            'activeAllocation' => $instActiveAllocation,
            'programYear' => $programYear,
            'submittedApps' => $claimCounts->submitted ?? 0,
            'holdApps' => $claimCounts->hold ?? 0,
            'claimedApps' => $claimCounts->claimed ?? 0,
            'reportingCompleteApps' => $claimCounts->reportingcomplete ?? 0,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('institution::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('institution::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('institution::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
