<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');

        $credentials[$this->username()] = strtolower($credentials[$this->username()]);

        return $credentials;
    }

    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $username = $this->username();

        // Retrieve the user by lowercase username
        $user = User::where(DB::raw("LOWER({$username})"), $credentials[$username])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $this->guard()->login($user, $request->filled('remember'));
            return true;
        }

        return false;
    }

    public function username()
    {
        return "username";
    }
}
