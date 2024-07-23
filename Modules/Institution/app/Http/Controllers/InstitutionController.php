<?php

namespace Modules\Institution\Http\Controllers;

use App\Events\StaffRoleChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionStaffEditRequest;
use App\Models\Allocation;
use App\Models\Claim;
use App\Models\InstitutionStaff;
use App\Models\ProgramYear;
use App\Models\Role;
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



        $cacheProgramYear = Cache::get('global_program_years', function () {
            $programYears = ProgramYear::orderBy('id')->get();
            $programYear = ProgramYear::where('status', 'active')->first();
            return [
                'list' => $programYears,
                'default' => $programYear->guid
            ];
        });

        $programYear = ProgramYear::where('guid', $cacheProgramYear['default'])->first();

        // Eager load active allocation
        $institution->load(['allocations' => function ($query) {
//            $query->where('status', 'active')->orderByDesc('created_at');
            $query->orderByDesc('created_at');
        }]);

        $instAllocation = $institution->allocations->first();
        if(!is_null($instAllocation)){
            // Combine claim counts into a single query
//            $claimCounts = Claim::where('institution_guid', $institution->guid)
//                ->where('allocation_guid', $instActiveAllocation->guid)
//                ->selectRaw("
//            SUM(CASE WHEN claim_status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
//            SUM(CASE WHEN claim_status = 'Hold' THEN 1 ELSE 0 END) as hold,
//            SUM(CASE WHEN claim_status = 'Claimed' AND reporting_completed_date IS NULL THEN 1 ELSE 0 END) as claimed,
//            SUM(CASE WHEN claim_status = 'Claimed' AND reporting_completed_date IS NOT NULL THEN 1 ELSE 0 END) as reportingcomplete
//        ")
//                ->first();

            $claimCounts = Claim::where('institution_guid', $institution->guid)
                ->where('allocation_guid', $instAllocation->guid)
                ->selectRaw("
        SUM(CASE WHEN claim_status = 'Claimed' THEN COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) ELSE 0 END) as claimed,
        SUM(CASE WHEN claim_status = 'Hold' THEN COALESCE(estimated_hold_amount, 0) ELSE 0 END) as hold
    ")
                ->first();
            return Inertia::render('Institution::Dashboard', [
                'results' => $institution,
                'activeAllocation' => $instAllocation,
                'programYear' => $programYear,
                'holdApps' => $claimCounts->hold ? (float)$claimCounts->hold + ((float)$claimCounts->hold / (float)$programYear->claim_percent): 0,
                'claimedApps' => $claimCounts->claimed ? (float)$claimCounts->claimed + ((float)$claimCounts->claimed / (float)$programYear->claim_percent) : 0,
            ]);
        }


        return Inertia::render('Institution::Dashboard', [
            'results' => $institution,
            'activeAllocation' => $instAllocation,
            'programYear' => $programYear,
            'holdApps' => 0,
            'claimedApps' => 0,
        ]);
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $institution = $user->institution;

        return Inertia::render('Institution::Institution', ['institution' => $institution]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function staffList(Request $request): \Inertia\Response
    {
        $user = User::find(Auth::user()->id);
        $institution = $user->institution->staff()->with('user.roles')->get();

        return Inertia::render('Institution::Staff', ['status' => true, 'results' => $institution]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function staffUpdate(InstitutionStaffEditRequest $request): \Inertia\Response
    {
        InstitutionStaff::where('id', $request->id)->update($request->validated());
        $user = User::find(Auth::user()->id);
        $institution = $user->institution->staff;

        return Inertia::render('Institution::Staff', ['status' => true, 'results' => $institution]);
    }

    /**
     * Update the specified resource role in storage.
     */
    public function staffUpdateRole(Request $request): \Inertia\Response
    {
        $newRole = Role::where('name', Role::Institution_GUEST)->first();
        if ($request->input('role') === 'User') {
            $newRole = Role::where('name', Role::Institution_USER)->first();
        }

        $rolesToCheck = [Role::Ministry_ADMIN, Role::SUPER_ADMIN, Role::Institution_ADMIN, Role::Institution_USER];
        if (Auth::user()->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && Auth::user()->disabled === false) {
            $staff = InstitutionStaff::where('id', $request->input('id'))->first();

            if (! is_null($staff)) {
                //reset roles
                $roles = Role::whereIn('name', [Role::Institution_ADMIN, Role::Institution_USER, Role::Institution_GUEST])->get();
                foreach ($roles as $role) {
                    $staff->user->roles()->detach($role);
                }

                $staff->user->roles()->attach($newRole);
                event(new StaffRoleChanged($staff->user, $newRole));
            }
        }

        $user = User::find(Auth::user()->id);
        $institution = $user->institution->staff;

        return Inertia::render('Institution::Staff', ['status' => true, 'results' => $institution]);
    }
}
