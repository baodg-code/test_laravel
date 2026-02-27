<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $token = $user->createToken('web-session-token')->plainTextToken;
            $tokenId = (int) explode('|', $token, 2)[0];

            $request->session()->put('api_token', $token);
            $request->session()->put('api_token_id', $tokenId);

            if ($user?->is_admin) {
                return redirect()->route('admin.index');
            }

            return redirect()->route('products.index');
        }

        return back()->withErrors([
            'email' => 'Email or password is incorrect.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $tokenId = (int) $request->session()->get('api_token_id', 0);

        if ($user && $tokenId > 0) {
            $user->tokens()->whereKey($tokenId)->delete();
        }

        Auth::logout();

        $request->session()->forget(['api_token', 'api_token_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
