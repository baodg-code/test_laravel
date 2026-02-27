<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $data['password'] = Hash::make($data['password']);
        $data['is_admin'] = $request->boolean('is_admin');

        User::create($data);

        return redirect()->route('admin.index', ['tab' => 'users']);
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validatedData($request, $user->id, false);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_admin'] = $request->boolean('is_admin');

        $user->update($data);

        return redirect()->route('admin.index', ['tab' => 'users']);
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('admin.index', ['tab' => 'users']);
    }

    private function validatedData(Request $request, ?int $userId = null, bool $requirePassword = true): array
    {
        $passwordRule = $requirePassword ? 'required|min:6' : 'nullable|min:6';

        return $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:120|unique:users,email,' . ($userId ?? 'NULL') . ',id',
            'password' => $passwordRule,
        ]);
    }
}
