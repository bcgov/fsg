<?php

namespace Modules\Institution\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class IsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $roles = empty($roles) ? [null] : $roles;

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        //if the user is disabled or has neither idir/bceid
        if ($user->disabled || (is_null($user->bceid_user_guid))) {
            Auth::logout();

            return redirect()->route('login');
        }

        //active user must have at least a Ministry User role
        if ($user->roles->isEmpty()) {
            $role = Role::where('name', Role::Institution_GUEST)->first();
            $user->roles()->attach($role);

            return redirect()->route('login')->with([
                'loginAttempt' => true,
                'hasAccess' => false,
                'status' => 'Please contact your Institution Admin to grant you access.',
            ]);
        }
        if (! $user->hasRole(Role::SUPER_ADMIN) && ! $user->hasRole(Role::Institution_ADMIN) && ! $user->hasRole(Role::Institution_USER)) {
            return redirect()->route('login')->with([
                'loginAttempt' => true,
                'hasAccess' => false,
                'status' => 'Please contact the Institution Admin to verify your access.',
            ]);
        }

        return $next($request);
    }
}
