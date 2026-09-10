<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    /**
     * Log a visitor straight into the shared demo hospital — no credentials.
     * Guarded by config('hms.demo.enabled'); the account is the read-limited
     * "Demo" role and its data is rebuilt daily by hms:reset-demo.
     */
    public function enter(Request $request): RedirectResponse
    {
        abort_unless(config('hms.demo.enabled'), 404);

        if (Auth::check() && Auth::user()->email !== config('hms.demo.email')) {
            return redirect()->route('dashboard');
        }

        $demo = User::where('email', config('hms.demo.email'))
            ->where('is_active', true)
            ->first();

        abort_unless($demo, 404);

        Auth::login($demo);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status',
            "You're exploring the ".config('app.name').' demo — go ahead and add patients, book appointments, admit to a bed. Everything resets each night.');
    }
}
