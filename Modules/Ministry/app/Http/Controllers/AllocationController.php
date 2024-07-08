<?php

namespace Modules\Ministry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AllocationEditRequest;
use App\Http\Requests\AllocationStoreRequest;
use App\Models\Allocation;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Response;

class AllocationController extends Controller
{
    public function fetchAllocations(Request $request, ?Allocation $allocation = null)
    {
        $body = Allocation::where(['institution_guid' => $request->input('institution_guid'), 'status' => 'active'])->get();
        if (! is_null($allocation)) {
            $body = $allocation;
        }

        return Response::json(['status' => true, 'body' => $body]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AllocationStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $allocation = Allocation::create($request->validated());

        $allocation = Allocation::find($allocation->id);
        return Redirect::route('ministry.institutions.show', [$allocation->institution->id, 'allocations']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AllocationEditRequest $request): \Illuminate\Http\RedirectResponse
    {
        $allocation_id = Allocation::where('id', $request->id)->update($request->validated());
        $allocation = Allocation::find($request->id);
        return Redirect::route('ministry.institutions.show', [$allocation->institution->id, 'allocations']);
    }
}
