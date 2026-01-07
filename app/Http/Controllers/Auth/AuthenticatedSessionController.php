<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
    
        // Check if the authenticated user is disabled
        if (Auth::user()->disable_login) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been disabled by admin.']);
        }
        //Flash success message before regeneration
        $request->session()->flash('login_success', 'Login successfully ...');
        $request->session()->regenerate();
    
        return redirect()->intended(route('dashboard', absolute: false));
    }
    

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
