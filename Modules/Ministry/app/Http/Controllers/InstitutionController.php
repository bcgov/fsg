<?php

namespace Modules\Ministry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionEditRequest;
use App\Models\Country;
use App\Models\Institution;
use App\Models\Program;
use App\Models\ProgramYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Response;

class InstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $institutions = $this->paginateInst();

        return Inertia::render('Ministry::Institutions', ['status' => true, 'results' => $institutions]);
    }

    /**
     * Show the specified resource.
     */
    public function show(Institution $institution, $page = 'details')
    {
        $institution = Institution::where('id', $institution->id)->with(
            ['allocations.py', 'activeAllocation', 'staff.user.roles', 'programs']
        )->first();

        $countries = Cache::remember('countries', 380, function () {
            return Country::where('active', true)->orderBy('name')->get();
        });
        $program_years = Cache::remember('program_years_ministry', 380, function () {
            return ProgramYear::orderBy('guid')->get();
        });

        return Inertia::render('Ministry::Institution', ['page' => $page, 'results' => $institution,
            'countries' => $countries, 'programYears' => $program_years]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InstitutionEditRequest $request): RedirectResponse
    {
        Institution::where('id', $request->id)->update($request->validated());

        return Redirect::route('ministry.institutions.show', [$request->id]);
    }

    private function paginateInst()
    {
        $institutions = Institution::with('activeAllocation');

        if (request()->filter_name !== null) {
            $institutions = $institutions->where('name', 'ILIKE', '%'.request()->filter_name.'%');
        }

        if (request()->sort !== null) {
            $institutions = $institutions->orderBy(request()->sort, request()->direction);
        } else {
            $institutions = $institutions->orderBy('name');
        }

        return $institutions->paginate(25)->onEachSide(1)->appends(request()->query());
    }

    public function fetchPrograms(Request $request)
    {
        $institution_guid = $request->input('in');
        $programs = Program::where('institution_guid', $institution_guid)->IsActive()->get();

        return Response::json(['status' => true, 'body' => $programs]);
    }
}
