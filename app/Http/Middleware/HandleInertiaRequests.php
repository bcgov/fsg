<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = null;
        if (Auth::check()) {
            $user = User::find(Auth::user()->id);
        }

        $sortedUtils = Cache::remember('sorted_utils', 3600, function () { //for an hour
            return Util::getSortedUtils();
        });
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
                'roles' => is_null($user) ? null : $user->roles,
                'readOnly' => Session::has('read-only'),
            ],
            'utils' => $sortedUtils,
            'ziggy' => function () {
                return (new Ziggy)->toArray();
            },
            'logoutUrl' => env('KEYCLOAK_LOGOUT_URL'),
//            'logoutBcscUrl' => Session::get('kc_logout'),
            'logoutBcscUrl' => Session::get('bcsc_logout_uri'),
        ]);

    }
}
