<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InstitutionStaff;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Stevenmaguire\OAuth2\Client\Provider\Keycloak;

class UserController extends Controller
{
    /**
     * Display first page after login (dashboard page)
     */
    public function home(Request $request)
    {
        return Inertia::render('Home');
    }

    public function appLogin(Request $request)
    {
        $provider = new Keycloak([
            'authServerUrl' => env('KEYCLOAK_SERVER_URL'),
            'realm' => env('KEYCLOAK_REALM'),
            'clientId' => env('KEYCLOAK_CLIENT_ID'),
            'clientSecret' => env('KEYCLOAK_CLIENT_SECRET'),
            'redirectUri' => env('KEYCLOAK_REDIRECT_URI'),
        ]);

        return $this->loginUser($request, $provider, Role::Ministry_GUEST);
    }

    public function bcscLogin(Request $request)
    {
        $provider = new Keycloak([
            'authServerUrl' => env('KEYCLOAK_BCSC_SERVER_URL'),
            'realm' => env('KEYCLOAK_BCSC_REALM'),
            'clientId' => env('KEYCLOAK_BCSC_CLIENT_ID'),
            'clientSecret' => env('KEYCLOAK_BCSC_CLIENT_SECRET'),
            'redirectUri' => env('KEYCLOAK_BCSC_REDIRECT_URI'),
            'scopes' => 'openid profile email',  // Scopes as a space-separated string
        ]);

        // This is needed to have the provider logout url formatted correctly
        $provider->setVersion('18.0.0');

        return $this->loginUser($request, $provider, Role::Student);
    }

    public function bceidLogin(Request $request)
    {
        $provider = new Keycloak([
            'authServerUrl' => env('KEYCLOAK_BCEID_SERVER_URL'),
            'realm' => env('KEYCLOAK_REALM'),
            'clientId' => env('KEYCLOAK_BCEID_CLIENT_ID'),
            'clientSecret' => env('KEYCLOAK_BCEID_CLIENT_SECRET'),
            'redirectUri' => env('KEYCLOAK_BCEID_REDIRECT_URI'),
        ]);

        return $this->loginUser($request, $provider, Role::Institution_GUEST);
    }

    private function loginUser(Request $request, $provider, $type): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {

        if (! $request->has('code')) {
            \Log::info('No code');
            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl([
                'scope' => 'openid profile email', // Ensure scopes include 'openid'
            ]);

            $request->session()->put('oauth2state', $provider->getState());
            \Log::info('$authUrl: '.$authUrl);
            \Log::info('$provider->getState(): '.$provider->getState());

            return Redirect::to($authUrl.'&kc_idp_hint=fsg');

            // Check given state against previously stored one to mitigate CSRF attack
        } elseif (! $request->has('state') || ($request->state !== $request->session()->get('oauth2state'))) {
            \Log::info('messed up state '.$request->state.' !== '.$request->session()->get('oauth2state'));
            $request->session()->forget('oauth2state');

            //Invalid state, make sure HTTP sessions are enabled
            return Inertia::render('Auth/Login', [
                'loginAttempt' => true,
                'hasAccess' => false,
                'status' => 'We could not log you in. Please contact RequestIT@gov.bc.ca',
            ]);
        } else {
            // Try to get an access token (using the authorization coe grant)
            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $request->code,
                ]);
            } catch (\Exception $e) {
                return Inertia::render('Auth/Login', [
                    'loginAttempt' => true,
                    'hasAccess' => false,
                    'status' => 'Failed to get access token: '.$e->getMessage(),
                ]);
            }

            // Optional: Now you have a token you can look up a users profile data
            try {
                // We got an access token, let's now get the user's details
                $provider_user = $provider->getResourceOwner($token);
                $provider_user = $provider_user->toArray();

                //this is needed for BCSC
                $tokenValues = $token->getValues();
                if (isset($tokenValues['id_token'])) {
                    $idToken = $tokenValues['id_token'];
                    $request->session()->put('bcsc_logout_uri', env('KEYCLOAK_BCSC_LOGOUT_URL').'?state='.
                        $request->state.'&scope=profile%20email&response_type=code&approval_prompt=auto&client_id=fsg&id_token_hint='.
                        $idToken.'&post_logout_redirect_uri='.env('KEYCLOAK_BCSC_REDIRECT_LOGOUT_URI'));

                    $returnUrl = env('KEYCLOAK_LOGOUT_URL1') . '?retnow=1&returl=' . urlencode(env('KEYCLOAK_LOGOUT_URL2').'?id_token_hint=' . $idToken . '&post_logout_redirect_uri=' . env('KEYCLOAK_LOGOUT_URL3'));
                    $request->session()->put('kc_logout_uri', $returnUrl);
                }
                \Log::info('KC Logout : '.$provider->getLogoutUrl(['access_token' => $token]));
//                \Log::info('idToken: ');
//                \Log::info($token->getValues());
                \Log::info('We got a token: '.$token);
                \Log::info('$provider_user: '.json_encode($provider_user));
            } catch (\Exception $e) {
                \Log::info(' ');
                return Inertia::render('Auth/Login', [
                    'loginAttempt' => true,
                    'hasAccess' => false,
                    'status' => 'Failed to get resource owner: '.$e->getMessage(),
                ]);
            }

            $user = null;
            $failMsg = null;
            if ($type === Role::Student) {
                if (! isset($provider_user['bcsc_user_guid'])) {
                    $failMsg = 'Session conflict. Please use incognito window';
                } else {
                    $user = User::where('bcsc_user_guid', 'ilike', $provider_user['bcsc_user_guid'])->first();
                    $failMsg = 'Welcome back!.';
                }
            }
            if ($type === Role::Ministry_GUEST) {
                $user = User::where('idir_user_guid', 'ilike', $provider_user['idir_user_guid'])->first();
                $failMsg = 'Welcome back! Please contact Ministry Admin to grant you access.';
            }
            if ($type === Role::Institution_GUEST) {
                $user = User::where('bceid_user_guid', 'ilike', $provider_user['bceid_user_guid'])->first();
                $failMsg = 'Welcome back! Please contact Institution Admin to grant you access.';
            }

            //if it is a new BCSC, IDIR or BCeID user, register the user first
            if (is_null($user)) {
                [$valid, $user] = $this->newUser($provider_user, $type);
                if ($valid == '200' && $type === Role::Student) {

                    Cache::put('bcsc_provider_user_' . $user->id, json_encode($provider_user));
                    Auth::login($user);

                    \Log::info(' ');
                    return Redirect::route('student.home');

                } elseif ($valid == '200' && $type !== Role::Student) {
                    \Log::info(' ');
                    return Inertia::render('Auth/Login', [
                        'loginAttempt' => true,
                        'hasAccess' => false,
                        'status' => 'Please contact Admin to grant you access.',
                    ]);
                } else {
                    \Log::info(' ');
                    return Inertia::render('Auth/Login', [
                        'loginAttempt' => true,
                        'hasAccess' => false,
                        'status' => $valid,
                    ]);
                }

                //if the user has been disabled
            } elseif ($user->disabled === true) {
                \Log::info(' ');
                return Inertia::render('Auth/Login', [
                    'loginAttempt' => true,
                    'hasAccess' => false,
                    'status' => 'Access denied. Please contact Admin.',
                ]);
            }

            $user->name = $provider_user['name'];
            $user->save();
            \Log::info('We got a name: '.$provider_user['name']);

            //else the user has access
            if ($type === Role::Ministry_GUEST) {
                //check if the user is a guest
                $rolesToCheck = [Role::Ministry_GUEST];
                if ($user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty()) {
                    \Log::info(' ');
                    return Inertia::render('Auth/Login', [
                        'loginAttempt' => true,
                        'hasAccess' => false,
                        'status' => $failMsg,
                    ]);
                }

                Auth::login($user);

                \Log::info(' ');
                return Redirect::route('ministry.home');
            }

            if ($type === Role::Student) {
                Cache::put('bcsc_provider_user_' . $user->id, json_encode($provider_user));
                Auth::login($user);

                \Log::info(' ');
                return Redirect::route('student.home');
            }

            if ($type === Role::Institution_GUEST) {
                //check if the user is a guest
                $rolesToCheck = [Role::Institution_GUEST];
                if ($user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty()) {
                    \Log::info(' ');
                    return Inertia::render('Auth/Login', [
                        'loginAttempt' => true,
                        'hasAccess' => false,
                        'status' => $failMsg,
                    ]);
                }

                Auth::login($user);

                \Log::info(' ');
                return Redirect::route('institution.dashboard');
            }

            \Log::info(' ');
            return Redirect::route('login');
        }
    }

    /**
     * Display the login view.
     *
     * @return \Inertia\Response
     */
    public function login(Request $request)
    {
        return Inertia::render('Auth/Login', [
            'loginAttempt' => false,
            'hasAccess' => false,
            'status' => session('status'),
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function newUser($provider_user, $type)
    {
        $valid = '200';
        $user = null;
        if ($type === Role::Ministry_GUEST && isset($provider_user['idir_username']) && $provider_user['idir_username']) {
            $check = User::where('idir_username', Str::upper($provider_user['idir_username']))->first();
            if (! is_null($check)) {
                $valid = 'This IDIR is already in use. Please contact the admin.';
            }
        } elseif ($type === Role::Institution_GUEST && isset($provider_user['bceid_username']) && $provider_user['bceid_username']) {
            $check = User::where('bceid_username', Str::upper($provider_user['bceid_username']))->first();
            if (! is_null($check)) {
                $valid = 'This BCeID is already in use. Please contact the admin.';
            }
        } elseif ($type === Role::Student && isset($provider_user['bcsc_user_guid']) && $provider_user['bcsc_user_guid']) {
            $check = User::where('bcsc_user_guid', Str::upper($provider_user['bcsc_user_guid']))->first();
            if (! is_null($check)) {
                $valid = 'This BC Services Card is already in use. Please contact the admin.';
            }
            if (! isset($provider_user['email'])) {
                \Log::info('Your BC Services Card is missing a required Email Address. Please resolve that and try again.');
                $valid = 'Your BC Services Card is missing a required Email Address. Please resolve that and try again.';
            }
        } else {
            $valid = 'You are not authorized to access this page.';
        }

        if ($valid === '200') {
//            $email = isset($provider_user['email']) ? Str::lower($provider_user['email']) : null;
            $user = new User();
            $user->guid = Str::orderedUuid()->getHex();
            $user->name = Str::title($provider_user['name']);
            $user->first_name = Str::title($provider_user['given_name'] ?? Str::title($provider_user['family_name']));
            $user->last_name = Str::title($provider_user['family_name']);
            $user->email = Str::lower($provider_user['email']);
            $user->disabled = false;
            $user->bcsc_username = isset($provider_user['bcsc_username']) ? Str::upper($provider_user['bcsc_username']) : null;
            $user->idir_username = isset($provider_user['idir_username']) ? Str::upper($provider_user['idir_username']) : null;
            $user->bceid_username = isset($provider_user['bceid_username']) ? Str::upper($provider_user['bceid_username']) : null;
            $user->bcsc_user_guid = isset($provider_user['bcsc_user_guid']) ? Str::upper($provider_user['bcsc_user_guid']) : null;
            $user->idir_user_guid = isset($provider_user['idir_user_guid']) ? Str::upper($provider_user['idir_user_guid']) : null;
            $user->bceid_user_guid = isset($provider_user['bceid_user_guid']) ? Str::upper($provider_user['bceid_user_guid']) : null;
            $user->bceid_business_guid = isset($provider_user['bceid_business_guid']) ? Str::upper($provider_user['bceid_business_guid']) : null;
            $user->password = Hash::make(Str::lower($provider_user['email']));
            $user->save();
            $this->checkRoles($user, $type);

            if (isset($provider_user['bceid_business_guid'])) {
                \Log::info('isset bceid $provider_user');
                $this->checkInstitutionStaff($user, $provider_user);
            } elseif (isset($provider_user['bcsc_user_guid'])) {
                \Log::info('isset bcsc $provider_user');
                $this->addNewStudent($user, $provider_user);
            } else {
                \Log::info('net set $provider_user');
            }
        }

        return [$valid, $user];
    }

    private function checkInstitutionStaff($user, $provider_user)
    {
        $user = User::find($user->id);
        $institution = Institution::where('bceid_business_guid', $user->bceid_business_guid)->first();
        $institutionStaff = InstitutionStaff::where('bceid_user_guid', $user->bceid_user_guid)->with('institution')->first();

        // If the ministry did not setup any user with that bceid_business_guid then don't auto register
        if (! is_null($institution) && is_null($institutionStaff)) {
            \Log::info('$institution is not null and staff is');

            $staff = new InstitutionStaff();
            $staff->guid = Str::orderedUuid()->getHex();
            $staff->user_guid = $user->guid;
            $staff->institution_guid = $institution->guid;
            $staff->bceid_business_guid = $user->bceid_business_guid;
            $staff->bceid_user_guid = $user->bceid_user_guid;
            $staff->bceid_user_id = Str::upper($provider_user['bceid_username']);
            $staff->bceid_user_name = Str::title($provider_user['name']);
            $staff->bceid_user_email = Str::lower($provider_user['email']);
            $staff->status = 'Active';
            $staff->save();
        } else {
            \Log::info('$institution no go');
        }

        if (is_null($institution)) {
            \Log::info('no institution for bceid_business_guid: '.$user->bceid_business_guid);
        }
        if (is_null($institutionStaff)) {
            \Log::info('no staff for bceid_business_guid: '.$user->bceid_business_guid);
        }

    }

    //new user to be assigned as guest
    private function checkRoles($user, $type)
    {
        if (is_null($user->roles()->first())) {
            $role = Role::where('name', $type)->first();
            $user->roles()->attach($role);
        }
    }

    private function addNewStudent($user, $provider_user)
    {
        $user = User::find($user->id);
        $student = Student::where('dob', $provider_user['birthdate'])->where('email', $provider_user['email'])->first();

        if (is_null($student)) {
            \Log::info('New Student: '.$provider_user['email']);

            // Prevent this since some accounts are existing with sin.
            //            $st = new Student();
            //            $st->guid = Str::orderedUuid()->getHex();
            //            $st->user_guid = $user->guid;
            //            $st->first_name = Str::title($provider_user['given_name']);
            //            $st->last_name = Str::title($provider_user['family_name']);
            //            $st->email = $provider_user['email'];
            //            $st->dob = $provider_user['birthdate'];
            //            $st->gender = $provider_user['gender'];
            //
            //            $st->save();
        } else {
            \Log::info('Can not create New Student: '.$provider_user['email']);
        }
    }
}
