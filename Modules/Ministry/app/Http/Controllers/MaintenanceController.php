<?php

namespace Modules\Ministry\Http\Controllers;

use App\Events\ProgramYearUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\FaqEditRequest;
use App\Http\Requests\FaqStoreRequest;
use App\Http\Requests\ProgramYearEditRequest;
use App\Http\Requests\ProgramYearStoreRequest;
use App\Http\Requests\UtilEditRequest;
use App\Http\Requests\UtilStoreRequest;
use App\Models\Claim;
use App\Models\Faq;
use App\Models\Institution;
use App\Models\InstitutionStaff;
use App\Models\ProgramYear;
use App\Models\Role;
use App\Models\User;
use App\Models\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Response;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function staffList(Request $request): \Inertia\Response
    {
        $staff = User::with('roles')
            ->whereHas('roles', function ($q) {
                return $q->whereIn('name', [Role::Ministry_ADMIN, Role::Ministry_USER, Role::Ministry_GUEST]);
            })->orderBy('created_at', 'desc')->get();

        foreach ($staff as $user) {
            if ($user->roles->contains('name', Role::Ministry_ADMIN)) {
                $user->access_type = 'A';
            } elseif ($user->roles->contains('name', Role::Ministry_USER)) {
                $user->access_type = 'U';
            } else {
                $user->access_type = 'G';
            }
        }

        return Inertia::render('Ministry::Maintenance', ['status' => true, 'results' => $staff, 'page' => 'staff']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function updateStatus(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        if (Gate::denies('update', $user)) {
            abort(403);
        }
        $user->disabled = $request->input('disabled');
        $user->save();

        return Redirect::route('ministry.maintenance.staff.list');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function updateRole(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        if (Gate::denies('update', $user)) {
            abort(403);
        }
        $newRole = Role::where('name', Role::Ministry_GUEST)->first();
        if ($request->input('role') === 'Admin') {
            $newRole = Role::where('name', Role::Ministry_ADMIN)->first();
        }
        if ($request->input('role') === 'User') {
            $newRole = Role::where('name', Role::Ministry_USER)->first();
        }

        //reset roles
        $roles = Role::whereIn('name', [Role::Ministry_ADMIN, Role::Ministry_USER, Role::Ministry_GUEST])->get();
        foreach ($roles as $role) {
            $user->roles()->detach($role);
        }

        $user->roles()->attach($newRole);
        //        event(new StaffRoleChanged1($user, $newRole));

        return Redirect::route('ministry.maintenance.staff.list');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function utilList(Request $request): \Inertia\Response
    {
        $utils = Util::orderBy('field_name', 'asc')->get();

        $cat_utils = [];
        $cat_titles = [];
        foreach ($utils as $util) {
            $cat_utils[$util->field_type][] = $util;
        }
        foreach ($cat_utils as $k => $v) {
            $cat_titles[] = $k;
        }
        sort($cat_titles);

        return Inertia::render('Ministry::Maintenance', ['status' => true, 'results' => $cat_utils,
            'categories' => $cat_titles, 'page' => 'utils']);
    }

    /**
     * Update a utility resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function utilUpdate(UtilEditRequest $request, Util $util): \Illuminate\Http\RedirectResponse
    {
        $util->update($request->validated());
        $sortedUtils = Util::getSortedUtils();
        Cache::put('sorted_utils', $sortedUtils, 3600);

        return Redirect::route('ministry.maintenance.utils.list');
    }

    /**
     * Store a utility resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function utilStore(UtilStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        Util::create($request->validated());
        $sortedUtils = Util::getSortedUtils();
        Cache::put('sorted_utils', $sortedUtils, 3600);

        return Redirect::route('ministry.maintenance.utils.list');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function pyList(Request $request): \Inertia\Response
    {
        $programYears = ProgramYear::orderBy('start_date', 'asc')->get();

        return Inertia::render('Ministry::Maintenance', ['status' => true, 'results' => $programYears,
            'page' => 'program_years']);
    }

    /**
     * Update a utility resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function pyUpdate(ProgramYearEditRequest $request, ProgramYear $programYear): \Illuminate\Http\RedirectResponse
    {
        $programYear->update($request->validated());
//        Cache::forget('global_program_years');

        event(new ProgramYearUpdated($programYear, $request->status));

        return Redirect::route('ministry.maintenance.program_years.list');
    }

    /**
     * Store a utility resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function pyStore(ProgramYearStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        ProgramYear::create($request->validated());
//        Cache::forget('global_program_years');

        return Redirect::route('ministry.maintenance.program_years.list');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function faqList(Request $request): \Inertia\Response
    {
        $faqs = Faq::orderBy('order', 'asc')->get();

        return Inertia::render('Ministry::Maintenance', ['status' => true, 'results' => $faqs,
            'page' => 'faqs']);
    }

    /**
     * Update a utility resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function faqUpdate(FaqEditRequest $request, Faq $faq): \Illuminate\Http\RedirectResponse
    {
        $faq->update($request->validated());

        return Redirect::route('ministry.maintenance.faqs.list');
    }

    /**
     * Store a utility resource.
     *
     * @return \Illuminate\Http\RedirectResponse::render
     */
    public function faqStore(FaqStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        Faq::create($request->validated());

        return Redirect::route('ministry.maintenance.faqs.list');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function reportsSummary(Request $request): \Inertia\Response
    {
        $activePy = ProgramYear::active()->first();
        return Inertia::render('Ministry::Reports', ['results' => null, 'page' => 'summary', 'py' => $activePy]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function reportsDetail(Request $request): \Inertia\Response
    {
        $institutions = Institution::select('guid', 'name', 'category')->with('activeCaps')->orderBy('name')->get();
        $categories = Institution::select('category')->whereNotNull('category')->groupBy('category')->orderBy('category')->get();

        return Inertia::render('Ministry::Reports', ['results' => ['institutions' => $institutions, 'categories' => $categories], 'page' => 'detail']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function reportSources(Request $request): \Inertia\Response
    {
        $activePy = ProgramYear::active()->first();
        return Inertia::render('Ministry::Reports', ['results' => null, 'page' => 'sources', 'py' => $activePy]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response::render
     */
    public function reportSourcesFetch(Request $request, $from, $to, $type)
    {
        $fromDate = $from;
        $toDate = $to.' 23:59:59';

        if ($type === 'claims') {
            $rows = Claim::select(
                \DB::raw('NOW()'),
                'claims.*',
                'institutions.guid',
                'institutions.name as institution_name',
                'institutions.size',
                'institutions.address1',
                'institutions.city',
                'institutions.postal_code',
                'programs.guid',
                'programs.program_name',
                'programs.delivery_method',
                'programs.online_delivery_type',
                'programs.credential_type',
                'programs.high_priority_industry',
                'programs.creditable',
                'programs.full_time',
                'programs.prov_funded_micro_cred',
                'programs.indigenous_related_learning',
                'programs.diversity_inclusion_related_learning',
                'programs.active_status',
                'economic_regions.fsa',
                'economic_regions.region as claim_economic_region'
            )
                ->join('institutions', 'institutions.guid', '=', 'claims.institution_guid')
                ->join('programs', 'programs.guid', '=', 'claims.program_guid')
                ->leftJoin('economic_regions', \DB::raw('SUBSTRING(claims.zip_code, 1, 3)'), '=', 'economic_regions.fsa')

                ->whereBetween('claims.created_at', [$fromDate, $toDate])
                ->get();
        }
        if ($type === 'staff') {
            $rows = InstitutionStaff::select(
                \DB::raw('NOW()'),
                'institution_staff.institution_guid',
                'institution_staff.bceid_user_name',
                'institutions.guid as institution_guid',
                'institutions.name'
            )
                ->join('institutions', 'institutions.guid', '=', 'institution_staff.institution_guid')
                ->whereBetween('institution_staff.created_at', [$fromDate, $toDate])
                ->get();
        }

        $csvData = [];
        $csvDataHeader = [];
        if ($rows->isEmpty()) {
            return 'No results for the date range selected.';
        }

        // Capture column names dynamically
        $attributes = $rows->first()->getAttributes();
        foreach ($attributes as $k => $v) {
            $csvDataHeader[] = $k;
        }

        // Iterate through fetched data to build CSV rows
        foreach ($rows as $attestation) {
            $rowData = [];
            foreach ($attributes as $k => $v) {
                $rowData[] = $attestation->{$k};
            }
            $csvData[] = $rowData;
        }

        // Generate CSV file
        $output = fopen('php://temp', 'w');
        fputcsv($output, $csvDataHeader);

        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $response = Response::make(stream_get_contents($output), 200);
        $response->header('Content-Type', 'text/csv');
        $response->header('Content-Disposition', 'attachment; filename='.$request->type.'_data.csv');
        fclose($output);

        return $response;

    }

    /**
     * Display a listing of the resource.
     */
    public function reportsSummaryFetch(Request $request)
    {
        $activePy = ProgramYear::active()->with('allocations.institution')->first();

        $fromDate = $request->from_date;
        $toDate = $request->to_date.' 23:59:59';

        $publicReport = ['instList' => [], 'total' => 0, 'Claimed' => 0, 'Hold' => 0];

        foreach ($activePy->allocations as $allocation){
            $publicReport['instList'][$allocation->institution->name] = [
                'total' => $allocation->total_amount,
                'Claimed' => 0, 'Hold' => 0
            ];
            $publicReport['total'] += $allocation->total_amount;
        }


        // Fetch attestations within the specified date range
        $results = Claim::with('institution')->whereBetween('created_at', [$fromDate, $toDate])
            ->whereIn('claim_status', ['Submitted', 'Hold', 'Claimed'])
            ->get();

        foreach ($results as $claim) {
            $this->updateReport($claim, $publicReport);
        }

        return response()->json([
            'status' => true,
            'body' => [
                'publicReport' => $publicReport,
            ],
        ]);
    }

    private function addInstToReport($inst, &$report)
    {
        if (! isset($report[$inst->category])) {
            $report['public'] = ['instList' => [], 'total' => 0, 'claimed' => 0, 'hold' => 0, 'submitted' => 0];
        }

        $total = is_null($inst->activeCaps->first()) ? 0 : $inst->activeCaps->first()->total_attestations;
        $report[$inst->category]['instList'][$inst->name] = [
            'total' => $total,
            'issued' => 0,
            'draft' => 0,
        ];

        $report[$inst->category]['total'] += $total;
        $report['total'] += $total;
    }

    private function updateReport($claim, &$report)
    {
        $inst = $claim->institution;
        $instName = $inst->name;
        $status = $claim->claim_status;

        if($status == 'Hold'){
            $report['instList'][$instName][$status] += $claim->estimated_hold_amount;
            $report[$status] += $claim->estimated_hold_amount;
        }
        if($status == 'Claimed'){
            $report['instList'][$instName][$status] += $claim->registration_fee + $claim->program_fee + $claim->materials_fee + $claim->correction;
            $report[$status] += $claim->registration_fee + $claim->program_fee + $claim->materials_fee + $claim->correction;
        }
//
//
//        $report['public']['instList'][$instName][$status] += $claim->total_amount;
//        $report['public'][$status] += $claim->total_amount;
//        $report[$status] += $claim->total_amount;
    }

    private function getReportType($category)
    {
        return in_array($category, ['College', 'Teaching University', 'University']) ? 'public' : 'private';
    }

    /**
     * Display a listing of the resource.
     */
    public function reportsDetailFetch(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date.' 23:59:59';

        // Fetch all institutions with active attestations
        $institutions = Institution::with(['activeCaps.attestations'])->whereHas('activeCaps')->get();

        // Initialize report arrays
        $publicReport = ['total' => 0, 'issued' => 0, 'draft' => 0];
        $privateReport = ['total' => 0, 'issued' => 0, 'draft' => 0];

        // Add missing institutions that have not issued any attestation
        foreach ($institutions as $inst) {
            $instType = $this->getReportType($inst->category);
            if ($instType === 'public') {
                $this->addInstToReport($inst, $publicReport);
            }
            if ($instType === 'private') {
                $this->addInstToReport($inst, $privateReport);
            }
        }

        // Fetch attestations within the specified date range
        $results = Attestation::with('institution')->whereBetween('created_at', [$fromDate, $toDate])->get();

        // Update report based on fetched attestations
        foreach ($results as $att) {
            $reportType = $this->getReportType($att->institution->category);
            if ($reportType === 'public') {
                $this->updateReport($att, $publicReport);
            }
            if ($reportType === 'private') {
                $this->updateReport($att, $privateReport);
            }
        }

        return response()->json([
            'status' => true,
            'body' => [
                'publicReport' => $publicReport,
                'privateReport' => $privateReport,
            ],
        ]);
    }
}
