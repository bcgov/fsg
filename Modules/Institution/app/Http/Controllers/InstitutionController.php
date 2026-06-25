<?php

namespace Modules\Institution\Http\Controllers;

use App\Events\StaffRoleChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionStaffEditRequest;
use App\Models\AllocationFundingType;
use App\Models\Claim;
use App\Models\InstitutionStaff;
use App\Models\ProgramYear;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
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

        $cacheProgramYear = Cache::remember('global_program_years_' . $user->institution->guid, now()->addHours(1), function () use ($user){
            $programYears = ProgramYear::orderBy('id')->get();
            $programYear = ProgramYear::where('status', 'active')->first();

            $programs = $user->institution->programs
                ->sortBy('program_name') // Sort by program_name in ascending order
                ->pluck('program_name', 'guid')
                ->toArray();

            return [
                'list' => $programYears,
                'default' => $programYear->guid,
                'programs' => $programs,
            ];
        });

        $programYear = ProgramYear::where('guid', $cacheProgramYear['default'])->first();

        // Load all active allocations for the program year (a program year may contain several).
        $institution->load(['allocations' => function ($query) use ($programYear) {
            $query->where('program_year_guid', $programYear->guid)->where('status', 'active');
            $query->orderByDesc('created_at');
        }]);

        $claimPercent = (float) $programYear->claim_percent;

        // Adds the admin fee to a net claim amount so figures match the allocation accounting.
        $withAdmin = function ($amount) use ($claimPercent) {
            $amount = (float) $amount;
            return ($claimPercent > 0 && $amount) ? $amount + ($amount / $claimPercent) : $amount;
        };

        // Build a per-allocation, per-funding-type summary for the dashboard cards.
        $allocationSummaries = $institution->allocations->map(function ($allocation) use ($institution, $withAdmin) {
            $fundingTypes = AllocationFundingType::where('allocation_guid', $allocation->guid)
                ->orderBy('funding_type')
                ->get();

            // One card per funding type defined on this allocation.
            $fundingTypeCards = $fundingTypes->map(function ($ft) use ($institution, $allocation, $withAdmin) {
                $hold = Claim::where('institution_guid', $institution->guid)
                    ->where('allocation_guid', $allocation->guid)
                    ->where('claim_status', 'Hold')
                    ->where('funding_type', $ft->funding_type)
                    ->sum(\DB::raw('COALESCE(estimated_hold_amount, 0)'));

                $claimed = Claim::where('institution_guid', $institution->guid)
                    ->where('allocation_guid', $allocation->guid)
                    ->where('claim_status', 'Claimed')
                    ->where('funding_type', $ft->funding_type)
                    ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'));

                $allocated = (float) $ft->amount;
                $holdWithAdmin = $withAdmin($hold);
                $claimedWithAdmin = $withAdmin($claimed);

                return [
                    'funding_type' => $ft->funding_type,
                    'allocated' => $allocated,
                    'hold' => $holdWithAdmin,
                    'claimed' => $claimedWithAdmin,
                    'remaining' => $allocated - $holdWithAdmin - $claimedWithAdmin,
                ];
            })->values();

            // Legacy fallback: allocations created before funding types existed have none defined.
            // For those we show combined hold/claimed only (no funding-type split, no TS cards).
            $legacyHold = null;
            $legacyClaimed = null;
            if ($fundingTypes->isEmpty()) {
                $legacyHold = $withAdmin(
                    Claim::where('institution_guid', $institution->guid)
                        ->where('allocation_guid', $allocation->guid)
                        ->where('claim_status', 'Hold')
                        ->sum(\DB::raw('COALESCE(estimated_hold_amount, 0)'))
                );
                $legacyClaimed = $withAdmin(
                    Claim::where('institution_guid', $institution->guid)
                        ->where('allocation_guid', $allocation->guid)
                        ->where('claim_status', 'Claimed')
                        ->sum(\DB::raw('COALESCE(program_fee, 0) + COALESCE(materials_fee, 0) + COALESCE(registration_fee, 0) + COALESCE(correction_amount, 0)'))
                );
            }

            return [
                'guid' => $allocation->guid,
                'total_amount' => (float) $allocation->total_amount,
                'has_funding_types' => $fundingTypes->isNotEmpty(),
                'funding_types' => $fundingTypeCards,
                'legacy_hold' => $legacyHold,
                'legacy_claimed' => $legacyClaimed,
            ];
        })->values();

        // Claims awaiting outcome reporting across all active allocations for the program year.
        $waitingOutcome = Claim::where('institution_guid', $institution->guid)
            ->whereIn('allocation_guid', $institution->allocations->pluck('guid'))
            ->where('claim_status', 'Claimed')
            ->whereNull('outcome_effective_date')
            ->whereNull('outcome_status')
            ->count();

        return Inertia::render('Institution::Dashboard', [
            'results' => $institution,
            'programYear' => $programYear,
            'allocationSummaries' => $allocationSummaries,
            'waitingOutcome' => $waitingOutcome,
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
