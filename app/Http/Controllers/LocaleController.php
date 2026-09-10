<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the UI language. Works for guests (session + cookie) and persists
     * to the user row when signed in.
     */
    public function update(Request $request): RedirectResponse
    {
        $allowed = array_keys(config('hms.locales', ['en' => 'English']));

        $data = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', $allowed)],
        ]);

        $request->session()->put('locale', $data['locale']);

        if ($user = $request->user()) {
            $user->forceFill(['locale' => $data['locale']])->save();
        }

        return back()->withCookie(cookie('locale', $data['locale'], 60 * 24 * 365));
    }
}
