<?php

namespace Modules\Institution\Http\Controllers;

use App\Events\AttestationDraftUpdated;
use App\Events\AttestationIssued;
use App\Http\Controllers\Controller;
use App\Models\Allocation;
use App\Models\Claim;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Modules\Institution\App\Http\Requests\AttestationEditRequest;
use Response;

class ClaimController extends Controller
{
    protected $countries;

    protected $allocations;

    public function __construct()
    {
        $this->allocations = Allocation::where('status', 'active')->with('py')->orderByDesc('created_at')->get();
        $this->countries = Country::select('name')->where('active', true)->get();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the inst cap and check if we have hit the cap for issued attestations
        // This is going to be all attes. under this inst. and are using the same fed cap as this.
        $user = User::find(Auth::user()->id);
        $institution = $user->institution;
        $allocation = $this->allocations->where('institution_guid', $institution->guid)->first();

        $user = User::find(Auth::user()->id);

        $claims = $this->paginateClaims($user->institution, $allocation);

        return Inertia::render('Institution::Claims', ['error' => null, 'results' => $claims,
            'institution' => $user->institution,
            'countries' => $this->countries,
            'allocation' => $allocation]);
    }

    private function paginateClaims($institution, $allocation)
    {

        if(is_null($allocation)) {
           return null;
        }
        $claims = Claim::where('institution_guid', $institution->guid)
            ->where('allocation_guid', $allocation->guid)
            ->whereNotIn('claim_status', ['Draft'])
            ->with('student', 'program');

        if (request()->filter_term !== null && request()->filter_type !== null) {
            $claims = match (request()->filter_type) {
                'snumber' => $claims->where('student_number', 'ILIKE', '%'.request()->filter_term.'%'),
                'fname' => $claims->where('first_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'lname' => $claims->where('last_name', 'ILIKE', '%'.request()->filter_term.'%'),
                'travel_id' => $claims->where('id_number', 'ILIKE', '%'.request()->filter_term.'%'),
                'pal_id' => $claims->where('fed_guid', 'ILIKE', '%'.request()->filter_term.'%'),
                'city' => $claims->where('city', 'ILIKE', '%'.request()->filter_term.'%'),
                'country' => $claims->where('country', 'ILIKE', '%'.request()->filter_term.'%'),
            };
        }

        if (request()->sort !== null) {
            $claims = $claims->orderBy(request()->sort, request()->direction);
        } else {
            $claims = $claims->orderBy('created_at', 'desc');
        }

        return $claims->with('institution.allocations', 'institution.programs')->paginate(25)->onEachSide(1)->appends(request()->query());
    }
}
